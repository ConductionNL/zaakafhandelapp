# Proposal — accurate-appstore-metadata

## Why

The App Store metadata in `appinfo/info.xml` describes a **different product**.
The summary and description are a stale OpenCatalogi copy-paste: "Brinning
Gateway and Service bus functionality to nextcloud", "Synchronize your data
sources", "Send cloud eventt", "Map and translate api calls", with the
description body literally opening "The OpenCatalogi Nextcloud app provides a
framework for federated catalogi". None of this is what Zaak Afhandel App does,
and the in-description links point at OpenCatalogi GitBook/roadmap/issue
trackers instead of this repository. The summary is also truncated mid-word
("…made by ConductionN").

On top of describing the wrong product, the metadata makes two claims the
codebase does not back:

1. **"System Cron is currently required for this app to work"** — there are no
   `BackgroundJob` or Cron classes anywhere under `lib/` (verified by grep);
   the app registers no background jobs, so cron is not required.
2. **Elasticsearch** — the README architecture diagram, Requirements table and
   Tech Stack table promise "Elasticsearch (optional) — for full-text case
   search". Only the elastic *config keys* exist
   (`openspec/specs/app-configuration/spec.md`); no search behaviour is built
   on them, and per company constraint search is provided by OpenRegister —
   we do not re-implement it per app.

The README additionally promises "Message Audit Trail — Full history of
message edits **with revert capability**". The audit-trail *read* is real and
specced (`zgw-client-interaction` REQ-004), but no revert exists anywhere in
`appinfo/routes.php`, `src/`, or the specs. The README diagram also shows
"Nextcloud Activity" and "Cron → background jobs" nodes with no backing code.

This is flagged **high severity** in
`FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` ("Accurate App Store
metadata") and is recommendation #1 there: the current listing actively
misleads anyone evaluating the app from the Nextcloud App Store. This change
is the cheapest credibility win — pure metadata/documentation, no code.

## What Changes

- **REWRITE** `appinfo/info.xml` `<summary>` and `<description>` (English plus
  a Dutch `lang="nl"` variant of each) so they describe what the app actually
  is: Dutch municipal case handling (*zaakgericht werken*, VNG GEMMA/ZGW) on
  OpenRegister — cases (zaken) with zaaktypen, statussen, rollen, besluiten
  and resultaten; tasks (taken); klanten/personen/organisaties with
  contactmoment logging; in-case messaging with a message audit trail; a
  personal werkvoorraad dashboard; and a ZGW (ZRC/ZTC/DRC/BRC) REST API
  surface. The OpenRegister requirement line stays.
- **REMOVE** the false "System Cron is currently required for this app to
  work" line from `info.xml` (no BackgroundJob classes exist).
- **REPLACE** the OpenCatalogi GitBook/roadmap/bug-report/feature-request
  links in the description with the zaakafhandelapp repository equivalents.
- **ALIGN** `README.md` with the implemented surface:
  - drop "with revert capability" from the Message Audit Trail feature bullet
    (the history read stays — it is real); revert moves to a future change if
    ever wanted;
  - drop Elasticsearch from the architecture diagram, the Requirements table
    and the Tech Stack table — state that search is provided by OpenRegister;
  - drop the unbacked "Cron → background jobs" and "Nextcloud Activity" nodes
    from the architecture diagram.
- **NO app code changes.** Controllers, services, routes, frontend are
  untouched. The stubbed ZGW endpoints are handled by the separate
  `zgw-zrc-drc-completion` change.

## Impact

### Affected specs

- **NEW** `specs/appstore-metadata/spec.md` — declares the accuracy
  requirements for the App Store listing and README so the next copy-paste
  regression is caught by spec review (4 ADDED requirements).

### Affected files

- `appinfo/info.xml` — summary, description (en + nl), cron-claim removal,
  link corrections, patch version bump.
- `README.md` — revert-capability bullet, architecture diagram, Requirements
  table, Tech Stack table.

### Affected behaviour

- None at runtime. App Store listing and repository front page become
  truthful; no endpoint, page, or store changes.

### Citations

- `FEATURE-REEVALUATION-2026-06-11/zaakafhandelapp.md` — MISSING rows
  "Accurate App Store metadata" (high), "Elasticsearch full-text case search"
  (low, "prefer removing the claim"), "Background jobs / cron" (low, "doc fix
  — drop the cron claim"), "Message-edit revert" (med, "or correct the README
  claim"); Recommendation #1.
- `appinfo/info.xml` lines 6–26 (current wrong summary/description).
- `README.md` — feature bullet "Message Audit Trail … with revert
  capability", architecture diagram (Elasticsearch / Cron / Activity nodes),
  Requirements + Tech Stack tables.
- `openspec/specs/app-configuration/spec.md` — proves only the elastic
  *config keys* are specced, not any search behaviour.
