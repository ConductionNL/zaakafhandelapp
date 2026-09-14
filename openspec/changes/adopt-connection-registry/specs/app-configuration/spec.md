# app-configuration Specification Delta

**Status**: proposed
**Scope**: zaakafhandelapp
**OpenSpec changes**:
- [adopt-connection-registry](../../)

## Purpose

Admins see zaakafhandelapp's outside connections on one page, with a status the app can back.

## ADDED Requirements

### Requirement: REQ-ZAA-CONN-001 Zaakafhandelapp declares its outside connections in one static file

Zaakafhandelapp SHALL declare its outside connections in `lib/Settings/connections.json` in the shape of hydra connection-registry design D2 (hydra REQ-CONN-001). The file SHALL declare `zrc`, `brc`, `drc`, `ztc`, `orc`, `klanten`, `elastic` and `mongodb`, the eight sources `ConfigurationController` saves. `zrc` and `brc` SHALL require their location key, because `CallService` calls both. The other six SHALL be declared not available, with a message saying their settings are kept and nothing calls them yet. No entry SHALL carry a `settingsUrl`, because no admin section writes these keys.

#### Scenario: The declaration names this app and passes integriq's schema
@e2e exclude A static file with no browser surface; tests/Unit/Settings/ConnectionsDeclarationTest.php checks the shape, the app id, unique keys and the absent settings links.

- **GIVEN** `lib/Settings/connections.json`
- **WHEN** it is validated against integriq's `connections.schema.json`
- **THEN** it SHALL validate
- **AND** its `app` SHALL equal the id in `appinfo/info.xml`
- **AND** every key SHALL be unique

#### Scenario: A saved ZRC address reads configured
@e2e exclude Rule 5 runs in integriq and holds only until the first ZRC call reports, and other specs in the suite make ZRC calls; tests/Unit/Settings/ConnectionsDeclarationTest.php asserts the requiredConfig integriq reads, and the ZRC scenario under REQ-ZAA-CONN-002 covers the row in a browser run.

- **GIVEN** integriq has synced zaakafhandelapp's declaration
- **AND** no ZRC call has been reported yet
- **WHEN** an admin saves `zrcLocation` through `POST /api/configuration`
- **THEN** the ZRC row SHALL read Configured

#### Scenario: A saved but unused API reads not available
@e2e tests/e2e/workflows/integrations-page.spec.ts

- **GIVEN** integriq has synced zaakafhandelapp's declaration
- **WHEN** an admin reads the DRC, ZTC, ORC, Klanten, Elasticsearch and MongoDB rows
- **THEN** each row SHALL read Not available
- **AND** each message SHALL say the settings are kept and nothing calls the API yet

### Requirement: REQ-ZAA-CONN-002 A save asks integriq to look again and a ZGW call reports what it met

When a save through `ConfigurationController` writes a ZRC or BRC key, zaakafhandelapp SHALL send `ConnectionRefreshRequestedEvent` with app `zaakafhandelapp` and that connection key (hydra REQ-CONN-004). When `CallService` finishes a ZRC or BRC call, zaakafhandelapp SHALL report what the call met with `ConnectionStatusReportedEvent`: no answer, HTTP 401, 403, 502, 503 or 504 as `error`, an empty location as `unconfigured`, and any other answer as `configured`. It SHALL report only when the status differs from the last report and five minutes have passed, or when an hour has passed. A save that writes a key of that connection SHALL clear that memory, so the next call reports at once. Both events SHALL be named by string and sent only when the class exists. Neither SHALL change the response of the request that sent it.

#### Scenario: Saving a ZRC key asks for a refresh
@e2e exclude The event is not observable from a browser; tests/Unit/Service/ConnectionReportServiceTest.php and tests/Unit/Controller/ConfigurationControllerTest.php assert the refresh and the unchanged response.

- **GIVEN** integriq is installed
- **WHEN** an admin saves `zrcLocation`
- **THEN** zaakafhandelapp SHALL send a refresh request for `zrc` and none for `brc`

#### Scenario: A ZRC that does not answer reads error
@e2e tests/e2e/workflows/integrations-page.spec.ts

- **GIVEN** an admin saved a `zrcLocation` where nothing answers
- **WHEN** a ZRC call runs
- **THEN** zaakafhandelapp SHALL report `zrc` as `error` at once, without waiting out the report memory
- **AND** the ZRC row SHALL read Error with a message naming the host
- **AND** the call SHALL fail as it did before this change

#### Scenario: Calls that meet the same thing report once an hour
@e2e exclude Timing is not observable from a browser; tests/Unit/Service/ConnectionReportServiceTest.php drives the clock.

- **GIVEN** a ZRC call reported `configured` ten minutes ago
- **WHEN** another ZRC call gets an answer
- **THEN** zaakafhandelapp SHALL send no report

#### Scenario: Without integriq nothing is sent
@e2e exclude The CI instance installs integriq; tests/Unit/Service/ConnectionReportServiceTest.php asserts that nothing is sent, stored or logged when the class is absent.

- **GIVEN** integriq is not installed
- **WHEN** a ZRC call runs or an admin saves `zrcLocation`
- **THEN** no event SHALL be sent and nothing SHALL be logged
- **AND** no report memory SHALL be written to app config

### Requirement: REQ-ZAA-CONN-003 An admin reads the connections on an Integrations page

Zaakafhandelapp SHALL render an `index` page at `/settings/integrations` over `integriq/app_connection`, reached from the settings gear and preset to `app` equal to `zaakafhandelapp` through its menu entry's `query` (hydra REQ-CONN-006). The page and its menu entry SHALL be admin only. The page SHALL require Integriq, and the menu entry SHALL only render when integriq is installed. The status column SHALL name all six statuses, `limited` included. The page SHALL NOT offer a generic Add button. Its Add integration action SHALL open `/apps/integriq/connections?app=zaakafhandelapp&link=1`.

#### Scenario: The page lists only the rows of zaakafhandelapp
@e2e tests/e2e/workflows/integrations-page.spec.ts

- **GIVEN** zaakafhandelapp and integriq are installed and integriq has synced the declaration
- **WHEN** an admin opens the Integrations page
- **THEN** the page SHALL list the eight declared connections
- **AND** every listed row SHALL have `app` equal to `zaakafhandelapp`

#### Scenario: Add integration goes to integriq
@e2e tests/e2e/workflows/integrations-page.spec.ts

- **GIVEN** the Integrations page
- **WHEN** the admin chooses Add integration
- **THEN** the browser SHALL open integriq's Connections overview with `app=zaakafhandelapp` and `link=1`

#### Scenario: A connection that works in part reads Limited
@e2e exclude No zaakafhandelapp declaration or report produces limited; tests/vitest/connectionRegistry.spec.js asserts the label in English and Dutch.

- **GIVEN** a row whose status is `limited`
- **WHEN** the page renders it
- **THEN** the cell SHALL read Limited, or Beperkt on a Dutch instance
