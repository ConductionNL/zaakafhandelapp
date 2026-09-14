# adopt-connection-registry tasks

## 1. Declare

- [x] 1.1 Write `lib/Settings/connections.json` with the eight sources `ConfigurationController` saves.
- [x] 1.2 Guard the file in `tests/Unit/Settings/ConnectionsDeclarationTest.php`.

## 2. Page

- [x] 2.1 Add `src/manifest.d/connection-registry.json` with the page and its settings-gear menu entry.
- [x] 2.2 Add `src/services/connectionRegistry.js` with the two formatters and the Add integration handler.
- [x] 2.3 Wire the formatters in `src/App.vue` and the handler in `src/customComponents.js`; register `PowerPlugOutline` in `src/icons.js`.
- [x] 2.4 Add the strings to `l10n/en` and `l10n/nl`.
- [x] 2.5 Cover it in `tests/vitest/connectionRegistry.spec.js`.

## 3. Reports and refresh

- [x] 3.1 Add `lib/Service/ConnectionReportService.php`.
- [x] 3.2 Report ZRC and BRC call outcomes from `CallService`.
- [x] 3.3 Refresh from `ConfigurationController::save()`.
- [x] 3.4 Add the integriq event stubs to `tests/Stubs`, `tests/bootstrap.php` and `psalm.xml`.
- [x] 3.5 Cover it in `ConnectionReportServiceTest`, `CallServiceConnectionReportTest` and `ConfigurationControllerTest`.

## 4. End to end

- [ ] 4.1 Write `tests/e2e/workflows/integrations-page.spec.ts`.
- [ ] 4.2 Install integriq in the CI `additional-apps`.

## 5. After integriq ships

- [ ] 5.1 Run the e2e spec against an instance with both apps, then archive this change.
