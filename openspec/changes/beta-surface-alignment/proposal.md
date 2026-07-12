---
kind: code
---

# Proposal: beta-surface-alignment

## Why

Zaakafhandelapp's four public surfaces — `appinfo/info.xml`, `src/manifest.json`,
the conduction.nl product page, and the `docs/` Docusaurus site — had drifted
apart, and the product page in particular had accumulated fabricated claims
that do not match the shipped code. Before this app can be called beta-ready,
all four surfaces must agree on one vocabulary and every marketing/compliance
claim must be verifiable against `lib/` and `src/`.

## Verification method

Every claim below was checked directly against the code on `development`:
`grep`/`read` across `lib/Controller`, `lib/Service`, `appinfo/routes.php`,
and `src/manifest.json`. Nothing was taken on faith from the old product page
or from publiccode.yml.

## Canonical feature list (source of truth: `src/manifest.json` + `lib/Controller`)

1. **Cases (zaken)** — zaaktypen, statussen, rollen, eigenschappen, besluiten,
   resultaten (`ZakenController`, `ZaakTypenController`, `StatusenController`,
   `RollenController`, `BesluitenController`, `ResultatenController`,
   `ZaakEigenschappenController`).
2. **Opschorting / verlenging** — suspend/extend a running case
   (`ZGWZaakOpschortingVerlengingService`, `src/modals/zaken/SuspendZaak.vue`,
   `src/modals/zaken/ExtendZaak.vue`).
3. **Tasks (taken)** — assigned to medewerkers or klanten (`TakenController`;
   `taak.medewerker` / `taak.klant` filters used throughout the manifest).
4. **Klanten, personen, organisaties** — with contactmoment logging
   (`KlantenController`, `ContactMomentenController`).
5. **Medewerkers** — internal handler directory (`MedewerkersController`).
6. **In-case messaging (berichten)** — with a message/audit trail
   (`BerichtenController`).
7. **Documents (documenten)** — `DocumentenController`,
   `ZaakInformatieObjectenController`; Files integration widget on
   zaak/taak/besluit/document/bericht detail pages.
8. **Personal werkvoorraad dashboard** — stats widgets for open/active cases,
   open tasks, contact moments, persons, organisations (`src/manifest.json`
   `Dashboard` page).
9. **ZGW (ZRC/ZTC/DRC/BRC) REST API surface** — `appinfo/routes.php` `api/zrc`
   etc., `ObjectsController` and friends.
10. **Archive-date computation** — `ZGWArchiveDateService` computes the
    `archiefactiedatum` from the ZGW `afleidingswijze` rule on a resultaattype.
11. **Audit trail per case/object** — `ZaakAuditTrailController` + OpenRegister
    audit trail, surfaced as an `audit-trail` widget on every detail page.
12. **Task-assignment email notification** — `MailService` sends one outbound
    e-mail when a `taak.medewerker` field is set/changed (not a citizen
    mailbox integration).

Hard dependency: **OpenRegister** (storage, search, RBAC, audit) — already
declared correctly via `dependencies: ["openregister"]` in `src/manifest.json`.

## Fabricated claims removed (found only on the conduction.nl product page,
not in code)

| Claim (old product page) | Verification | Action |
|---|---|---|
| "Citizen-facing case-status portal", "jouwgemeente.nl/zaken" | No `#[PublicPage]` route, no citizen-facing frontend anywhere in `lib/Controller` or `src/` | **Removed.** Per project memory, a citizen-facing portal for this app is blocked pending an OpenConnector eHerkenning broker (openconnector#99 D1-D5) — not shipped today. |
| "DigiD or eIDAS login via OpenConnector" | No OpenConnector dependency in `src/manifest.json`/`composer.json`; no SAML/OIDC code in `lib/` | **Removed.** Do not claim DigiD/eIDAS until the broker exists and is wired. |
| "Mail, Files, Calendar, Deck" native integration | `lib/Service/MailService.php` only sends one outbound notification e-mail on task assignment (not Mail-app/citizen-inbox integration); no Calendar or Deck integration exists anywhere in `lib/` or `src/` | **Removed** Calendar/Deck/citizen-Mail claims; kept **Files** (real — `integration`/`files` widgets exist on zaak/taak/besluit/document/bericht detail pages). |
| "Gateway and service-bus functionality... routing/translating API calls between systems" (product page intro + `docs/intro.md` + `docs/FEATURES.md`) | No gateway/service-bus/cloud-event code found anywhere in `lib/` | **Removed** from the product page and from `docs/intro.md` / `docs/FEATURES.md`; replaced with the real ZGW REST API surface claim. |
| "TMLO and Archiefwet 2026 compliant", "DocuDesk archives automatically" | No DocuDesk dependency or call anywhere in `lib/`; no formal TMLO/Archiefwet certification exists | **Removed** the compliance/DocuDesk claims; kept the real, narrower claim — `ZGWArchiveDateService` computes `archiefactiedatum` from `afleidingswijze`. |
| "Workflow engine included" | No workflow-engine code found (`grep -rli workflow lib src` = no hits); only status-transition validation in `ZGWZaakValidationService` | **Removed** the "engine" framing from the product page's FeatureList. |
| publiccode.yml: "integrates with Open Zaak and other Dutch government API standards" | `OpenZakenWidget` = Dutch "open zaken" (open cases) dashboard widget, **not** an integration with the external Open Zaak (VNG) product; no such integration exists in `lib/` | **Fixed** in `publiccode.yml` (EN+NL) — replaced with the real ZGW REST API surface claim. |
| Version "v2.0" / status "Stable" (product page) | `info.xml` version is `0.2.7`; `publiccode.yml` said `developmentStatus: beta` | **Fixed** — product page now shows `v0.2.7` / status "Beta", matching info.xml (source of truth) and publiccode.yml. |
| `<licence>agpl</licence>` (info.xml) | `LICENSE` file, README badge, and publiccode.yml `legal.license` all already said EUPL-1.2 | **Fixed** — info.xml now declares `EUPL-1.2`, matching every other surface. |
| `publiccode.yml` `softwareVersion: 0.2.2` | `info.xml` says `0.2.7` | **Fixed** — bumped to `0.2.7` (info.xml is the version source of truth). |

## Claims kept as-is (verified true)

- ZGW (ZRC/ZTC/DRC/BRC) REST API surface — real, routed in `appinfo/routes.php`.
- OpenRegister hard dependency — real, declared in `src/manifest.json`.
- Opschorting/verlenging — real, service + two modals.
- Tasks assignable to klanten (not just medewerkers) — real, per manifest
  filters (`taak.klant=@objectId` on `KlantDetail`).
- Archive-date computation from `afleidingswijze` — real, narrower framing
  (no formal TMLO/Archiefwet certification claimed).
- App is superseded by Procest and Pipelinq for new deployments — already
  stated honestly in `docs/intro.md` and `docs/FEATURES.md`; carried through
  to the product page's "Pairs well with" section instead of contradicting it
  with a "Stable"/"v2.0" hero.

## Still misaligned / needs a decision

- **Core-apps list vs. deprecation.** `.claude/CLAUDE.md` still lists
  `zaakafhandelapp` among "Core Apps", while `docs/intro.md` and
  `docs/FEATURES.md` both carry a `:::warning Deprecated` banner saying the
  app "is no longer actively developed" and superseded by Procest/Pipelinq.
  This proposal did not resolve that contradiction (it's a portfolio/roadmap
  decision, not a surface-reconciliation one) — it only made sure the product
  page stops claiming "Stable"/"v2.0"/citizen-portal features that would
  contradict the deprecation notice already live in the app's own docs.
- **App icon** — `img/app.svg` is a plain white briefcase glyph, 24×24,
  `#ffffff` fill on a transparent background — matches the brand convention
  (white fill, 24×24) and needed no change.
