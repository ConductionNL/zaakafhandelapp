## 1. Bound the klanten-search alreadyLinked lookup

- [ ] 1.1 In `lib/Service/KlantContactSyncService.php`, replace `getLinkedContactsUids()` (`lib/Service/KlantContactSyncService.php:412-426`, currently `$this->objectService->getAllObjects(self::KLANT_TYPE)` with no limit) with a lookup scoped to only the uids present in the current addressbook search result set — e.g. rename to `filterAlreadyLinkedUids(array $contactUids): array` and have it issue a single filtered query (`getObjects(self::KLANT_TYPE, filters: ['contactsUid' => ['operator' => 'IN', 'value' => $contactUids]])` or equivalent mapper support) instead of loading every klant.
- [ ] 1.2 Update `searchContacts()` (`lib/Service/KlantContactSyncService.php:122-150`) to collect the uids from the (already-bounded, `limit: 50`) `$results` first, then call the new bounded lookup with just those uids, instead of calling `getLinkedContactsUids()` unconditionally before iterating results.
- [ ] 1.3 Confirm `OCA\OpenRegister\Service\ObjectService`'s mapper supports an `IN`/equivalent filter operator on a custom field (`contactsUid`); if not available, fall back to per-uid `findAll(['filters' => ['contactsUid' => $uid]], limit: 1)` calls bounded to the ≤50 search results — still strictly better than a full-register scan.
- [ ] 1.4 Add/extend `tests/Unit/Service/KlantContactSyncServiceTest.php::testSearchFlagsAlreadyLinked` (existing, `tests/Unit/Service/KlantContactSyncServiceTest.php:58-77`) to assert the mapper is invoked with a bounded/filtered call, not an unbounded `getAllObjects()`/`findAll(null, null)` — mock the mapper and assert on the filter arguments passed.

## 2. Tree-shake the lodash import

- [ ] 2.1 Replace `import _ from 'lodash'` with `import upperFirst from 'lodash/upperFirst'` and update call-sites in: `src/views/medewerkers/MedewerkerList.vue:75,40`, `src/sidebars/search/SearchSideBar.vue:228,26,121,176`, `src/views/klanten/KlantenList.vue:86,46`, `src/modals/medewerkers/SearchKlantModal.vue:161,106`, `src/modals/klanten/SearchKlantModal.vue:169,116`.
- [ ] 2.2 Replace `import _ from 'lodash'` with `import cloneDeep from 'lodash/cloneDeep'` and update call-sites in: `src/modals/zaken/AddRolToZaak.vue:47,121`, `src/modals/zaken/AddBerichtToZaak.vue:47,117`, `src/modals/zaken/AddTaakToZaak.vue:47,125`, `src/modals/contactMomenten/ContactMomentenForm.vue:620,960`, `src/modals/contactMomenten/ViewContactMoment.vue:275,503`.
- [ ] 2.3 Run `npm run lint` and `npm run build`; confirm no remaining `import _ from 'lodash'` (`grep -rn "from 'lodash'" src` returns only the two named-import forms).

## 3. Drop the dead fortawesome dependency

- [ ] 3.1 Confirm zero usages: `grep -rn "fortawesome" src` returns nothing (already verified at proposal time — re-verify at implementation time in case of drift).
- [ ] 3.2 Remove `@fortawesome/fontawesome-svg-core` and `@fortawesome/free-solid-svg-icons` from `package.json` (`package.json:35-36`).
- [ ] 3.3 Run `npm install` to regenerate `package-lock.json` without the removed packages; run `npm run build` to confirm the app still builds and no chunk references the removed packages.

## 4. Spec + traceability

- [ ] 4.1 Add `klanten-addressbook-sync` REQ-001 MODIFIED text (delta spec in this change) constraining the `alreadyLinked` computation to be bounded by the search result set, not the full register.
- [ ] 4.2 Run `openspec validate optimize-klanten-search-and-bundle-trim --strict` and resolve any errors.
- [ ] 4.3 Manual verify: open the klant search modal (`src/modals/klanten/SearchKlantModal.vue`) against a seeded register with several hundred klanten, confirm search results still correctly flag `alreadyLinked`, and confirm (via a temporary log line or Xdebug/profiler) that a search request no longer reads every klant row.
