# Design: adopt-connection-registry

The contract is hydra `openspec/changes/connection-registry/design.md` (hydra#667, amended in hydra#673). This file records how zaakafhandelapp meets it and where it fits loosely.

## D1. Which connections are declared

Each candidate was checked against the code on `development`, not against its name.

| Key | Declared as | Why |
|---|---|---|
| `zrc` | `requiredConfig: ["zrcLocation"]` | `CallService` is called with `source: 'zrc'` 30 times, from six controllers (statussen, rollen, resultaten, zaakobjecten, zaakinformatieobjecten, zaakeigenschappen). |
| `brc` | `requiredConfig: ["brcLocation"]` | `BesluitenController` calls `CallService` with `source: 'brc'` five times. |
| `drc`, `ztc`, `orc`, `klanten`, `elastic`, `mongodb` | `available: false` | `ConfigurationController` saves `{source}Location`, `{source}Key` and friends. A grep over `lib/` and `src/` finds no other reader. `ZGWRegistryService` maps `drc`, `ztc` and `brc` to OpenRegister register names, which is a different thing. |

**Why only the location is required.** `CallService::getAuthorization()` sends no auth header when `{source}AuthType` is empty, and an open ZRC works that way. Requiring the key would keep a working ZRC on Not configured.

**Why no row links to a settings section.** Contract D2 says to omit `settingsUrl` when no settings section exists. The admin page has one section, Data storage, and it writes `{objectType}_source`, `_register` and `_schema` through `SettingsController`. None of the declared keys. A link there would open a form where the admin cannot set a ZRC address. The `unconfiguredMessage` names the key and the two ways to set it instead. So no anchor is added either: nothing links to one.

**Why no `sourceTemplate`.** Integriq on `development` ships no ZGW source template.

**Why no `reportedOnly`.** Integriq can judge ZRC and BRC from app config under rule 5, and does so before any call has run.

## D2. What a call reports

`CallService::getClient()` adds a Guzzle `on_stats` callback. Guzzle calls it once per transfer, with a response or without one. The callback hands the HTTP status to `ConnectionReportService::reportCall()`.

| The call met | Status | Message |
|---|---|---|
| An empty `{source}Location` | `unconfigured` | "No ZRC address is set. Set zrcLocation to reach it." |
| No answer | `error` | "The last call to the ZRC at {host} got no answer." |
| HTTP 401 or 403 | `error` | "The ZRC at {host} refused the key (HTTP 401)." |
| HTTP 502, 503 or 504 | `error` | "The ZRC at {host} answered HTTP 503 on the last call." |
| Any other answer | `configured` | "The ZRC at {host} answered the last call." |

A 404 or a 400 is an answer about one request, not about the connection. A 500 is left out for the same reason: a ZRC that fails on one malformed body still serves the rest. The message names the host from the admin's own setting and nothing from the request, so no case data reaches the row.

**When it reports.** The report memory is one app-config value per source, `connection_report_zrc`, holding the last status and its time.

- A different status reports once five minutes have passed since the last report. Two endpoints that disagree cannot write a report on every request.
- The same status reports again after an hour, so `lastReport` stays newer than a stale probe.
- A save that writes a key of that connection clears the memory. The next call reports at once.

Without integriq the class check fails first, so nothing is read, stored, sent or logged. `reportCall()` catches everything, because it runs inside Guzzle's transfer and must never fail the call.

**Why this is cheap enough.** The callback costs a `class_exists` and one app-config read, which Nextcloud has already loaded for the request. A write and an event happen at most once an hour per source while nothing changes.

**The cost it accepts.** Under contract D4 a report outranks rule 5. Once a ZRC call has reported, a save alone cannot bring back "Required settings are filled.": the row keeps the last report until the next call, and the save only makes that next call report at once. Clearing `zrcLocation` behaves the same way, and the next call reports `unconfigured`. The e2e spec therefore drives a save and a call, and expects the call's report, not rule 5.

## D3. The refresh

`ConfigurationController::save()` hands the keys it wrote to `ConnectionReportService::refreshFromSave()`. For each connection whose keys are among them, the service clears the report memory and sends `ConnectionRefreshRequestedEvent('zaakafhandelapp', $key)`. The refresh keys are the location, the auth type and the key: the declared `requiredConfig` plus the two keys that change what a call meets. The six unused sources send nothing, because rule 2 decides their rows whatever the settings hold.

The controller and `CallService` take the service as an optional last argument. A missing service never breaks a save or a call.

## D4. The page

- `src/manifest.d/connection-registry.json`: an `index` page `Integrations` at `/settings/integrations`, `requiresApp` integriq, `permission: admin`, `showAdd: false`, and the columns dossiq uses: connection, status, status message, last checked, settings.
- Its menu entry `IntegrationsMenu` sits in the settings gear with `query: {app: zaakafhandelapp}`, `permission: admin` and `visibleIf.appInstalled: integriq`.
- `src/services/connectionRegistry.js` holds `connectionStatus`, `connectionSettingsLabel` and `openIntegriqConnections`.
- `App.vue` passes the formatters through CnAppRoot's `formatters` prop. `src/customComponents.js` carries the handler, because CnIndexPage resolves a header action's handler against `customComponents`.

The settings column stays, although no row fills it today. It is the contract's column, and a future admin section can link from it without a page change.

**Formatters.** The installed `@conduction/nextcloud-vue` 2.38.0 ships no `connectionStatus` built-in, so zaakafhandelapp carries a local copy with all six labels, `limited` included.

## D5. Contract misfits

- **A connection with no admin section.** Contract D2 assumes a key is set in a settings section or through `occ`. Zaakafhandelapp's keys are also set through its own REST endpoint. The declaration can only say so in a message.
- **A report outlives the settings it was about.** Rule 4b ranks any report above rule 5, and `ConnectionRefreshRequestedEvent` re-runs the resolver without touching `lastReport`. An app that reports call outcomes cannot let a save's filled settings speak until it makes another call. A possible amendment: a refresh for a key drops a `lastReport` older than the refresh, or rule 4b ignores it.
- **Basic auth cannot be configured.** `CallService` reads `zrcClientId` and `zrcSecret` for `basic`, and `ConfigurationController::WRITABLE_KEYS` holds neither. That is a finding for the app, not for the contract.

## Risks

- **ZRC may read Configured on an instance where a register import filled `zrcLocation`.** Rule 5 says "Required settings are filled.", which stays true.
- **The ZGW client belongs in integriq.** ADR-067 and ADR-091 put ZGW egress there (hydra `consume-shared-egress-fleet-wide`). When `CallService` moves, the report moves with it, and the declaration keeps its keys.
