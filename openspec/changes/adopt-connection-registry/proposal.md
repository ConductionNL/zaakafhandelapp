---
kind: code
---

# Proposal: adopt-connection-registry

## Why

Zaakafhandelapp stores the address and key of eight outside systems, and no screen says which of them work.

- Two are called. Thirty ZRC calls and five BRC calls go through `CallService`, from seven controllers.
- Six are saved and never read: DRC, ZTC, ORC, Klanten, Elasticsearch and MongoDB. `ConfigurationController` accepts them, and nothing else in `lib/` or `src/` reads them.
- No admin section writes any of these keys. Only `POST /api/configuration` and `occ config:app:set` do.

An admin who saves a ZTC address sees no difference from one who saves a ZRC address. The registry fixes that. Hydra change `connection-registry` (hydra#667, amended in hydra#673) gives every app one page of its connections, backed by integriq.

## What changes

- New `lib/Settings/connections.json` with eight connections. ZRC and BRC require their location key. The other six are declared not available, with a message saying the settings are kept and nothing calls them yet.
- An Integrations page under the settings gear, over integriq's `app_connection` schema, preset to `app=zaakafhandelapp`, admin only, and only shown when integriq is installed.
- Add integration opens `/apps/integriq/connections?app=zaakafhandelapp&link=1`.
- A save through `ConfigurationController` that writes a ZRC or BRC key asks integriq to resolve that connection again.
- `CallService` reports what a ZRC or BRC call met: an answer, a refused key, a gateway error or no answer. It reports on a change, and at most once an hour when nothing changes.
- Local `connectionStatus` and `connectionSettingsLabel` formatters with all six statuses, and the strings in English and Dutch.

## Depends on

- hydra `openspec/changes/connection-registry`, design D2, D4, D6, D8, D9 and D12.
- integriq on `development`: the `app_connection` schema, the declaration sync, both events and the Connections overview.

Without integriq the menu entry is hidden, a deep link shows the missing-dependency screen, and nothing is sent.

## Out of scope

- Moving the ZGW client into integriq. ADR-067 and ADR-091 say ZGW egress belongs there (hydra `consume-shared-egress-fleet-wide`). This change only reports on the client that exists.
- An admin screen for the ZGW keys. The Data storage section sets where object types are stored, not where the ZRC lives, so no row links to it.
- Basic authentication. `CallService` reads `zrcClientId` and `zrcSecret`, and `ConfigurationController` cannot write either key. That gap predates this change.

## Rollback

Revert the change. Zaakafhandelapp writes no rows of its own. Integriq removes the rows without a linked source on its next sync, and the report memory keys in app config stop being read.
