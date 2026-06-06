# settings

## Purpose

Settings is a custom manifest page (route `/settings`, settings-menu
entry "Settings") rendered by the `SettingsForm` custom component. It
lets an administrator choose, per object type (cases, tasks, customers,
messages, decisions, documents, results, roles, contact moments and
more), whether data is stored Internally or in an OpenRegister register
+ schema. It also surfaces an OpenRegister install prompt and per-type
save controls; values are loaded from and persisted to the app settings
endpoint.

## Requirements

### Requirement: Settings page renders

Navigating to the settings route SHALL render the settings page within
the app shell.

#### Scenario: Settings page renders

- **WHEN** a user navigates to the `/settings` route
- **THEN** the app-content area renders
- **AND** a "Data storage" settings section is present

### Requirement: Per-object-type storage source can be configured

Each object type SHALL expose a Source selector (Internal / OpenRegister)
with dependent Register and Schema selectors that appear when OpenRegister
is chosen.

#### Scenario: Storage source selectors are present

- **WHEN** the settings page has loaded
- **THEN** Source selectors for the object types are rendered
- **AND** a Save control is available

#### Scenario: Backend persistence of settings

- **WHEN** the user saves a storage-source configuration
- **THEN** the configuration is persisted via the settings endpoint
- @e2e exclude settings persistence is a backend behaviour of the app settings controller; covered by Newman/PHPUnit, not Playwright UI
