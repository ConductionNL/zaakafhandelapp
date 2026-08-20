## 1. ZaakDetails.vue

- [ ] 1.1 Wrap `Omschrijving:` (`src/views/zaken/ZaakDetails.vue:103`) as `{{ t('zaakafhandelapp', 'Description:') }}`.
- [ ] 1.2 Wrap `Zaaktype:` (`src/views/zaken/ZaakDetails.vue:108`) as `{{ t('zaakafhandelapp', 'Case type:') }}`.
- [ ] 1.3 Wrap the `v-tooltip="'bekijken'"` (`src/views/zaken/ZaakDetails.vue:112`) as `v-tooltip="t('zaakafhandelapp', 'View')"`, and add `:aria-label="t('zaakafhandelapp', 'View case type')"` to the same `NcButton` (icon-only, currently has no accessible name besides the tooltip).
- [ ] 1.4 Wrap `geen zaaktype gevonden` (`src/views/zaken/ZaakDetails.vue:118`) as `{{ t('zaakafhandelapp', 'No case type found') }}`.
- [ ] 1.5 Remove or replace the stale `Todo: Koppelings info met DSO` line (`src/views/zaken/ZaakDetails.vue:191`) — confirm with git blame/surrounding markup whether a real DSO-link section is pending; if so wrap a proper placeholder in `t()`, otherwise delete the line.
- [ ] 1.6 Wrap `View details` (`src/views/zaken/ZaakDetails.vue:214`) as `{{ t('zaakafhandelapp', 'View details') }}`.
- [ ] 1.7 Wrap `Er is geen audit trail gevonden voor deze zaak.` (`src/views/zaken/ZaakDetails.vue:221`) as `{{ t('zaakafhandelapp', 'No audit trail found for this case.') }}`.

## 2. ContactMomentenForm.vue

- [ ] 2.1 Wrap `Nieuw contactmoment` (line 22) as `{{ t('zaakafhandelapp', 'New contact moment') }}`.
- [ ] 2.2 Wrap `v-tooltip="'Sluiten'"` (line 34) as `v-tooltip="t('zaakafhandelapp', 'Close')"`.
- [ ] 2.3 Wrap both `Geen klant geselecteerd` occurrences (lines 83, 340) as `{{ t('zaakafhandelapp', 'No customer selected') }}`.
- [ ] 2.4 Wrap both `Persoon zoeken` occurrences (lines 97, 354) as `{{ t('zaakafhandelapp', 'Search person') }}`.
- [ ] 2.5 Wrap both `Organisatie zoeken` occurrences (lines 111, 368) as `{{ t('zaakafhandelapp', 'Search organisation') }}`.
- [ ] 2.6 Wrap both `Klant ontkoppelen` occurrences (lines 125, 382) as `{{ t('zaakafhandelapp', 'Unlink customer') }}`.
- [ ] 2.7 Wrap all three `Bekijken` occurrences (lines 188, 222, 256) as `{{ t('zaakafhandelapp', 'View') }}`.
- [ ] 2.8 Wrap both `Er zijn geen contactmomenten gevonden voor deze klant.` occurrences (lines 195, 429) as `{{ t('zaakafhandelapp', 'No contact moments found for this customer.') }}`.
- [ ] 2.9 Wrap both `Er zijn geen zaken gevonden voor deze klant.` occurrences (lines 229, 454) as `{{ t('zaakafhandelapp', 'No cases found for this customer.') }}`.
- [ ] 2.10 Wrap both `Er zijn geen taken gevonden voor deze klant.` occurrences (lines 263, 480) as `{{ t('zaakafhandelapp', 'No tasks found for this customer.') }}`.
- [ ] 2.11 Wrap both `Er zijn geen producten gevonden voor deze klant.` occurrences (lines 287, 503) as `{{ t('zaakafhandelapp', 'No products found for this customer.') }}`.
- [ ] 2.12 Wrap the `v-tooltip` at line 546 as `v-tooltip="t('zaakafhandelapp', 'A customer task can only be created once the contact moment is saved and a customer is selected.')"`.
- [ ] 2.13 Wrap the `v-tooltip` at line 557 as `v-tooltip="t('zaakafhandelapp', 'A case can only be started once the contact moment is saved and a customer is selected.')"`.

## 3. Extraction + translation

- [ ] 3.1 Run `node tests/l10n/check-l10n.js --write` to merge all newly-used keys into `l10n/en.json` (English source === key, per the tool's convention).
- [ ] 3.2 Add the corresponding Dutch translations for each new key to `l10n/nl.json` (values from the "What Changes" list in `proposal.md` — the original Dutch copy, so no user-visible change for nl-locale users).
- [ ] 3.3 Run `node tests/l10n/check-l10n.js` and `node tests/l10n/check-l10n-parity.js` (or `npm run test:l10n`) and confirm both pass clean.

## 4. Spec + traceability

- [ ] 4.1 Add `ui-modals` new Requirement "User-facing prose is translatable" (delta spec in this change, avoid REQ-006 — reserved by the in-flight `consolidate-audit-trail-modal` change — use REQ-007).
- [ ] 4.2 Add `ui-case-views` MODIFIED clause (delta spec in this change) extending an existing requirement to cover translatable prose in the case-detail view, or add a new requirement if none fits cleanly.
- [ ] 4.3 Run `openspec validate wrap-untranslated-detail-view-prose --strict` and resolve any errors.
- [ ] 4.4 Manual verify: switch the Nextcloud UI language to English (or any non-Dutch supported locale), open a zaak detail view and the contact-moment form, and confirm every string listed in `proposal.md` now renders in the selected language instead of unconditional Dutch.
