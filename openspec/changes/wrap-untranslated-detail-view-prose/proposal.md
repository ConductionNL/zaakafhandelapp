---
kind: code
---

# Proposal: wrap-untranslated-detail-view-prose

## Why

`tests/l10n/check-l10n.js` (`npm run test:l10n`) passes clean — 571 keys in
`l10n/en.json`, full nl parity — but that tool can only validate keys that
are **already** wrapped in a `t()`/`n()` call; it cannot detect user-visible
prose that was never wrapped in the first place. A manual pass over the two
heaviest detail/form views turns up a real, sizeable gap of exactly that
kind: hardcoded Dutch (and a little English) literal text shipped straight
into the template, invisible to the l10n tooling and therefore never
offered to translators and never following the user's selected locale.

**`src/views/zaken/ZaakDetails.vue`** (454 lines):
- `<h4>Omschrijving:</h4>` — `src/views/zaken/ZaakDetails.vue:103`
- `Zaaktype:` — `src/views/zaken/ZaakDetails.vue:108`
- `<NcButton v-tooltip="'bekijken'" ...>` — `src/views/zaken/ZaakDetails.vue:112` (icon-only button; the tooltip is the only user-facing label and it's a raw Dutch literal)
- `geen zaaktype gevonden` — `src/views/zaken/ZaakDetails.vue:118`
- `Todo: Koppelings info met DSO` — `src/views/zaken/ZaakDetails.vue:191` (a leftover developer TODO comment rendered as live UI text)
- `View details` — `src/views/zaken/ZaakDetails.vue:214` (English, but still unwrapped/untranslated for non-English locales)
- `Er is geen audit trail gevonden voor deze zaak.` — `src/views/zaken/ZaakDetails.vue:221`

**`src/modals/contactMomenten/ContactMomentenForm.vue`** (1447 lines) — the
same handful of strings repeated across the form's two view modes (compact
+ full), each occurrence a separate untranslated literal:
- `Nieuw contactmoment` — line 22
- `'Sluiten'` (close tooltip) — line 34
- `Geen klant geselecteerd` — lines 83, 340
- `Persoon zoeken` — lines 97, 354
- `Organisatie zoeken` — lines 111, 368
- `Klant ontkoppelen` — lines 125, 382
- `Bekijken` (repeated per related-record row) — lines 188, 222, 256
- `Er zijn geen contactmomenten gevonden voor deze klant.` — lines 195, 429
- `Er zijn geen zaken gevonden voor deze klant.` — lines 229, 454
- `Er zijn geen taken gevonden voor deze klant.` — lines 263, 480
- `Er zijn geen producten gevonden voor deze klant.` — lines 287, 503
- `'Een klant taak kan alleen worden aangemaakt als het contactmoment opgeslagen is en er een klant is geselecteerd.'` — line 546
- `'Een zaak kan alleen worden gestart als het contactmoment opgeslagen is en er een klant is geselecteerd.'` — line 557

Every one of these is user-visible: labels, empty-state messages, tooltips
on icon-only buttons, and disabled-action explanations. A non-Dutch admin
(the ADR-007 "English is the primary/source language" baseline, and the NL
Design System positioning that these apps support multiple languages) sees
Dutch text unconditionally in these two views regardless of their selected
Nextcloud language — the opposite of every other label in the same
components, which correctly goes through `t('zaakafhandelapp', '...')`
(e.g. `src/views/zaken/ZaakDetails.vue` uses `t()` for its page header and
action buttons elsewhere in the same file). This is a plain ADR-007
violation ("Hardcoded Dutch strings in code MUST be converted to English
keys with Dutch translations in `nl.json`") that the existing `check-l10n`
gate structurally cannot catch because it only audits already-wrapped keys.

## What Changes

- Wrap every literal listed above in `t('zaakafhandelapp', '<English
  source>')`, choosing an English key per ADR-007 (sentence case, English
  primary) and adding the matching Dutch translation to `l10n/nl.json`:
  - `Omschrijving:` → `t('zaakafhandelapp', 'Description:')`
  - `Zaaktype:` → `t('zaakafhandelapp', 'Case type:')`
  - `'bekijken'` tooltip → `t('zaakafhandelapp', 'View')`
  - `geen zaaktype gevonden` → `t('zaakafhandelapp', 'No case type found')`
  - `Todo: Koppelings info met DSO` → remove outright (a stale developer
    TODO comment, not real UI copy — confirm with the surrounding template
    whether the section still needs a placeholder, and if so replace with
    a proper `t()`-wrapped label, not a TODO note left in production markup)
  - `View details` → `t('zaakafhandelapp', 'View details')`
  - `Er is geen audit trail gevonden voor deze zaak.` → `t('zaakafhandelapp', 'No audit trail found for this case.')`
  - `Nieuw contactmoment` → `t('zaakafhandelapp', 'New contact moment')`
  - `'Sluiten'` → `t('zaakafhandelapp', 'Close')`
  - `Geen klant geselecteerd` → `t('zaakafhandelapp', 'No customer selected')`
  - `Persoon zoeken` → `t('zaakafhandelapp', 'Search person')`
  - `Organisatie zoeken` → `t('zaakafhandelapp', 'Search organisation')`
  - `Klant ontkoppelen` → `t('zaakafhandelapp', 'Unlink customer')`
  - `Bekijken` → `t('zaakafhandelapp', 'View')`
  - the four `Er zijn geen ... gevonden voor deze klant.` empty-states →
    `t('zaakafhandelapp', 'No contact moments found for this customer.')` /
    `'No cases found for this customer.'` / `'No tasks found for this
    customer.'` / `'No products found for this customer.'`
  - the two disabled-action tooltips → `t('zaakafhandelapp', 'A customer task can only be created once the contact moment is saved and a customer is selected.')` and `t('zaakafhandelapp', 'A case can only be started once the contact moment is saved and a customer is selected.')`
- Run `node tests/l10n/check-l10n.js --write` to extract the new keys into
  `l10n/en.json`, then add the Dutch values to `l10n/nl.json` (the existing
  `l10n-parity` check enforces this stays in sync going forward).
- Add the icon-only `NcButton` at `src/views/zaken/ZaakDetails.vue:112` an
  explicit `aria-label`/`:aria-label` in addition to the now-translated
  tooltip (currently relies solely on `v-tooltip`, which is not an
  accessible name for assistive tech) — a small, adjacent a11y fix bundled
  with this change since it touches the same line.

## Impact

- **Affected specs**: `ui-modals` (new REQ — user-facing prose must be
  translatable), `ui-case-views` (MODIFIED — same clause for case-detail
  views).
- **Affected code**: `src/views/zaken/ZaakDetails.vue`,
  `src/modals/contactMomenten/ContactMomentenForm.vue`, `l10n/en.json`,
  `l10n/nl.json`.
- No BREAKING changes — same visible copy for existing Dutch-locale users,
  now also correct for every other configured locale.
