# Zaakafhandelapp — portal contribution: the "Mijn Zaken" citizen portal

> Change kind: **code**. Tracking issue: Conduction/zaakafhandelapp#37.

## Why

Portaliq (hydra ADR-046) is the ONE shared external portal for people
**without** a Nextcloud account. Domain apps do not build their own portal;
they contribute a small, declarative manifest that portaliq discovers by
convention FQCN (`OCA\{Namespace}\Portal\PortalContributionProvider`) and
duck-types via `method_exists()` — never `instanceof`, never an info.xml
dependency. Without portaliq installed the provider is inert.

Zaakafhandelapp holds the Dutch citizen's case data (ZGW zaken, taken,
berichten). Citizens have a legal right to follow their own cases ("Mijn
Zaken"), but they have no Nextcloud account. This change contributes the
`citizen` read surfaces so portaliq can render a citizen's cases, tasks and
message inbox — scoped strictly to that citizen.

**Authentication decision (why we build now).** DigiD / eHerkenning is
**DEFERRED**. Citizens sign in through portaliq's ordinary
password / `portalAccount` edge at trust level **`low`** — exactly like
pipelinq's `client` / `customer` audiences — with **no broker dependency**.
Scoping is by a **server-managed `bsn` claim** on the citizen's portalAccount
(`claims.zaakafhandelapp.bsn`), resolved server-side, never client input — the
pipelinq `linkedContactId` pattern, using the ZGW-native citizen identifier.
The DigiD broker, when it lands, only raises assurance (an optional
trust-elevation of these same collections to `substantial`); it is not a
prerequisite for the portal.

## What Changes

- **Add `lib/Portal/PortalContributionProvider.php`** — a plain,
  dependency-free, duck-typed provider declaring the `citizen` audience and
  three READ collections, each BSN-scoped one hop into the domain:
  - `citizenZaken` — FORWARD via-join over `rol` (`rol.betrokkeneIdentificatie.inpBsn`
    == BSN → `rol.zaak`, `match: 'id'`), field-projected to citizen-safe case columns.
  - `citizenTaken` — REVERSE via-join over `klant` (`klant.bsn` == BSN → `taak.klant`,
    `match: 'scopeField'`), field-projected to the task's own facts.
  - `citizenBerichten` — the same reverse via-join applied to `bericht.gebruikerID`,
    surfaced as `kind: 'inbox'`, field-projected to the message body.
- **Add PHPUnit unit coverage** (`tests/Unit/Portal/PortalContributionProviderTest.php`
  + `tests/bootstrap.php`) pinning the manifest shape, the scoping map, the via
  structural contract, and a **register-drift pin** asserting every scope / via /
  projected field exists on its schema in the app's shipped data model.
- **Introduce PHPUnit** as a dev dependency (`composer.json` require-dev +
  `autoload-dev`) — the app had no PHP unit suite; this establishes it.

## Impact

- **Read-only.** No create-actions this wave. The portaliq writer stamps a
  create's scope field with the raw subjectRef pseudonym and does not resolve
  `scopeClaim`; since every collection scopes by the `bsn` claim, no create can
  be safely server-stamped yet, and a create referencing a specific zaak/klant
  would be a client-supplied cross-reference the writer cannot verify
  (write-IDOR, portaliq#16). Deferred with reasons in `design.md`.
- **Inert without portaliq.** No behaviour change to zaakafhandelapp itself; the
  provider is data-only and imported by nothing in this app.
- **Privacy.** Scoping is by BSN — sensitive PII. Every collection is read-only,
  field-projected to citizen-safe columns, and `design.md` records the intent to
  raise case data to `substantial` trust once DigiD lands.
- **Affected specs:** new capability `portal-contribution` (ADDED).
