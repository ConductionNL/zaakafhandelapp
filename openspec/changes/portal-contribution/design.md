# Design — Zaakafhandelapp portal contribution ("Mijn Zaken")

## Approach: declarative, not imperative

The contribution is a **declarative manifest** — pure data returned by
`getContribution()`, no I/O and no callbacks. Portaliq owns the runtime: its
`PortalObjectReader` reads OpenRegister directly (ADR-022), scopes every query
by the collection's declared scope value, re-verifies ownership per row, and
field-projects. Zaakafhandelapp only *declares* what a citizen may read; it
never runs the read. This keeps the security boundary in one audited place
(portaliq) and keeps the provider inert without portaliq.

The provider is a plain class: **no portaliq import, no `implements`, no
info.xml dependency, no constructor arguments** (contract amendment A1).
Portaliq discovers it by FQCN convention and duck-types it via
`method_exists()`. The FQCN is `OCA\ZaakAfhandelApp\Portal\PortalContributionProvider`
(namespace `ZaakAfhandelApp` from `composer.json` / `appinfo/info.xml`).

## Audiences

Only **`citizen`** (the ZGW natuurlijk-persoon data subject) is served.

`organisation` (a non-natuurlijk-persoon party — a company acting through
eHerkenning) is **deferred**: `rol.betrokkeneIdentificatie` cleanly exposes only
the natuurlijk-persoon BSN in the app's data model today; the org identity path
(KvK / RSIN / vestigingsnummer) is not modelled as a stable claimable field, so
scoping an org audience would require inventing a schema shape. Per "do not
invent", it waits until the org identifier claim path exists.

## The register at HEAD (what we verified against)

Zaakafhandelapp ships **no `lib/Settings/*_register.json`** — it is a ZGW app
whose schema-backed pages declare `register: "zaakafhandelapp"` in
`src/manifest.json` (with `dependencies: ["openregister"]`), and whose data
model is defined by the TypeScript entities under `src/entities/*.ts` (each with
a `zod` validator). Those entities — not the older `docs/json/*.json` ZGW export
snapshot (dated 2026-03-27, which pre-dates the app's flattened runtime shape) —
are the authoritative field source; the register-drift pin test anchors to them.
`docs/json/*.json` was used only to confirm `zaak.status` / `zaak.resultaat` /
`zaak.rollen`.

The `rol` runtime dot-path `betrokkeneIdentificatie.inpBsn` is not in the strict
`rol.ts` type (which declares `betrokkeneIdentificatie` as a loose object) but is
the documented runtime shape, evidenced at HEAD:

- `src/views/rollen/RolDetails.vue:60` — `rolStore.rolItem?.betrokkeneIdentificatie?.inpBsn`
- `lib/Controller/RollenController.php:118` — *"(BSN stored in betrokkeneIdentificatie.inpBsn) — fixes #279."*

## Scope-key decision (contract A4): the `bsn` claim

**Decision: scope by a server-managed `bsn` claim (`claims.zaakafhandelapp.bsn`),
not by a domain-object UUID.**

Contract A4 prefers a domain-object UUID over a bare identifier when the domain
cleanly offers one. It does **not** here for the citizen's own case ownership:

- `rol` — the canonical ZGW ownership link — carries **no klant/contact UUID**.
  Its `betrokkene` is a URL (and `betrokkeneType` may be non-person), and the
  citizen's stable identifier is the inline `betrokkeneIdentificatie.inpBsn`
  (verification quote above). There is no rol→klant UUID to prefer.
- BSN is a **legitimate ZGW domain identifier** — the natuurlijk-persoon citizen
  key — **not a Nextcloud uid**, so it satisfies A4's "never a Nextcloud account
  id" rule. It is the same value the password-edge identity mapping already
  knows, so it is always available (unlike a klant UUID, which only exists once
  the citizen has a klant record).

Using one `bsn` claim consistently across all three collections (rather than a
second `klantId` claim for taken/berichten) keeps the citizen's identity single
and always-provisionable; the klant hop is done as a **via-join** instead of a
claim.

### Privacy note (BSN is sensitive PII)

BSN is special-category-adjacent PII under the AVG. Mitigations, all enforced
here:

- **Read-only.** No collection writes; BSN is never accepted from the client —
  it is resolved server-side from the citizen's own portalAccount claim.
- **Never projected.** `bsn` / `inpBsn` / `gebruikerID` are used only as scope
  matchers and are **excluded from every `fields` whitelist**, so the BSN and
  the internal klant id never reach the browser.
- **Fail-closed.** An absent/malformed claim yields zero rows with no OR query
  (portaliq reader), so an unlinked account leaks nothing.
- **Trust.** Everything is `minTrust: 'low'` for the password edge **today**, but
  case data SHOULD be raised to **`substantial`** once the DigiD broker lands —
  see "minTrust raise-later" below.

## Claim-names contract (stable)

| Claim address | Meaning | Source |
|---|---|---|
| `claims.zaakafhandelapp.bsn` | The citizen's BSN (natuurlijk-persoon key) | Server-managed on the portalAccount by portaliq's password edge; declared here as bare `scopeClaim: "bsn"` → resolved in the contributing app's (`zaakafhandelapp`) namespace |

This is zaakafhandelapp's **only** claim and its stable contract. The bare
`"bsn"` form resolves in this app's namespace per the portaliq reader's
`parseClaimAddress`.

## Scoping map (audience `citizen`, all `minTrust: low`)

| Collection | Schema | scopeField | scopeClaim | via (register/schema/scopeField→targetField, match) | kind |
|---|---|---|---|---|---|
| `citizenZaken` | `zaak` | `""` (forward via ignores it) | `bsn` | `zaakafhandelapp` / `rol` / `betrokkeneIdentificatie.inpBsn` → `zaak`, **`id`** (forward) | — |
| `citizenTaken` | `taak` | `klant` | `bsn` | `zaakafhandelapp` / `klant` / `bsn` → `id`, **`scopeField`** (reverse) | — |
| `citizenBerichten` | `bericht` | `gebruikerID` | `bsn` | `zaakafhandelapp` / `klant` / `bsn` → `id`, **`scopeField`** (reverse) | `inbox` |

**How each join resolves (portaliq `PortalObjectReader`):**

- **Zaken — FORWARD (`match: 'id'`).** The join collects `rol.zaak` from every
  `rol` whose `betrokkeneIdentificatie.inpBsn` equals the citizen's BSN, then
  keeps zaken whose **own id** is in that set. A zaak is the citizen's precisely
  because a rol identifies them — the canonical ZGW ownership model. Staff roles
  (behandelaar etc.) are `betrokkeneType` non-person and carry no `inpBsn`, so
  they never match: no staff-case leak. (The zaak also has a convenience `klant`
  field, but rol is the canonical, standards-aligned ownership link, so we use
  it.)
- **Taken — REVERSE (`match: 'scopeField'`).** The join collects `klant.id` from
  every `klant` whose `bsn` equals the citizen's BSN, then keeps taken whose own
  `klant` reference is in that set (`taak.klant` holds the klant id).
- **Berichten — REVERSE (`match: 'scopeField'`).** Identical klant join applied
  to `bericht.gebruikerID` (which holds the klant id — `src/modals/berichten/EditBericht.vue`
  sets `gebruikerID: klantStore.klantItem?.id`). Surfaced as an `inbox`. The
  survey's second discriminator `soortGebruiker` ('Burger') cannot be enforced by
  the reader (single-field join), but it is unnecessary for safety: `gebruikerID`
  is a klant id unique to the citizen, and a medewerker message carries a
  medewerker id that can never be in the citizen's klant-id set.

**Integration note (URL vs UUID).** The forward `rol.zaak` targetField and the
reverse `taak.klant` / `bericht.gebruikerID` scope fields must hold OpenRegister
object references (bare id/uuid) for the join membership to match the outer
row's identifier. The strict ZGW entity types model some of these as URLs; the
OR-backed manifest stores relations as UUIDs (OR relations convention). This
contribution declares the canonical intent (rol references its zaak; taak/bericht
reference their klant); if the app's OR import stores a URL rather than a bare
ref, a normalisation shim is needed on the OR side. This is the same
proxy-vs-OR-relation tension the manifest-v1 change already flagged as a
follow-up; it is an OR-integration concern, not a provider defect.

## Field whitelists (citizen-safe projections)

Projection runs in the portaliq reader **after** per-row verification; the row
identifier (`id`/`uuid`) always survives, so detail links keep working. A
missing/renamed whitelist field is simply absent (fail-closed narrow).

### `citizenZaken` (schema `zaak`)

| Kept | | Dropped (reason) |
|---|---|---|
| `identificatie`, `omschrijving`, `toelichting` | | `rollen` — **other parties** on the case |
| `zaaktype`, `status`, `resultaat` | | `bronorganisatie`, `verantwoordelijkeOrganisatie` — internal org routing |
| `registratiedatum`, `startdatum`, `einddatum` | | `betalingsindicatie*`, `laatsteBetaaldatum`, `selectielijstklasse` — financial/archival internals |
| `einddatumGepland`, `uiterlijkeEinddatumAfdoening` | | `archiefstatus` — records-management internal |
| `publicatiedatum`, `communicatiekanaal` | | `url` — backend API topology; `hoofdzaak` — case-linkage internal |
| | | `klant`, `berichten` — scope/routing linkage (own collections) |

### `citizenTaken` (schema `taak`)

| Kept | | Dropped (reason) |
|---|---|---|
| `title`, `onderwerp`, `toelichting` | | `medewerker` — **staff handler identity** |
| `type`, `status`, `deadline`, `actie` | | `contactmoment` — internal contactmoment linkage |
| `zaak` (link back to the case) | | `klant` — scope-matcher (the citizen's own klant id) |

### `citizenBerichten` (schema `bericht`)

| Kept | | Dropped (reason) |
|---|---|---|
| `onderwerp`, `berichttekst`, `inhoud` | | `gebruikerID` — scope-matcher (internal klant id) |
| `berichtType`, `bijlageType`, `omschrijving` | | `soortGebruiker` — internal routing discriminator |
| `referentie`, `aanmaakDatum`, `publicatieDatum` | | `berichtLeverancierID`, `batchID`, `berichtID` — supplier/batch internals |
| | | `volgorde` — internal ordering; `title` — internal duplicate of `onderwerp` |

## Deferred: documents

Case documents are reachable from a zaak via `zaakinformatieobject` →
`enkelvoudiginformatieobject` — **two hops** from the citizen
(rol → zaak → zio → eio). Contract A5 permits **one** via-join only. Surfacing
documents would therefore need a materialised direct-BSN property on the document
schema (which does not exist) or a two-hop chain (forbidden). **Deferred**;
documents may also carry other-party content that needs its own projection
review. Follow-up on #37.

## Deferred: creates (read-first)

No create-actions this wave. Verified against portaliq
`lib/Service/PortalObjectWriter::createObject` at HEAD:

```php
if ($scopeField !== '') { $data[$scopeField] = $subjectRef; }
```

The writer stamps the scope field with the **raw subjectRef pseudonym** and does
**not** resolve `scopeClaim`. Every zaakafhandelapp collection scopes by the
`bsn` claim (or a via-join), never the raw subjectRef, so a stamped create would
be scoped to the pseudonym and be unreadable and domain-invalid:

- **createBericht** would need `gebruikerID` server-stamped with the citizen's
  klant id (resolved from BSN) — the writer cannot do that resolution.
- **any create referencing a zaak** (e.g. a message on a specific case) would
  require a client-supplied `zaak` cross-reference the writer cannot verify the
  citizen owns — a write-IDOR (the scholiq parent-create shape, portaliq#16).

Read-first is the correct, safe wave. A future change can add creates once the
writer can resolve `scopeClaim` and server-stamp a verified owner. Follow-up
on #37.

## minTrust raise-later (DigiD)

All collections are `minTrust: 'low'` **now**, matching the password edge. This
is a deliberate, documented interim: citizen case data SHOULD be gated at
**`substantial`** once the DigiD broker lands (DigiD delivers substantial-grade
assurance). Raising it is a one-line change per collection and requires no
provider restructuring — the collections already carry an explicit `minTrust`.
Tracked on #37.

## Seed Data (test fixtures)

Unit tests construct the provider directly (dependency-free — no container, no
mocks) and pass server-derived subject fixtures using **nil-pattern UUIDs**
(`00000000-0000-0000-0000-00000000000X`) — self-evidently fake, never colliding
with live data:

```
subjectRef  = 00000000-0000-0000-0000-000000000001
organisation = 00000000-0000-0000-0000-000000000002
audience     = citizen, trust = low
```

The `bsn` claim value is never needed in these tests — the provider is
declarative and does no lookups; the reader (portaliq) owns claim resolution.
