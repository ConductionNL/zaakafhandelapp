# berichten

## Purpose

Berichten (messages) records communications related to cases. The
manifest exposes a `bericht` index page (route `/berichten`) backed by
the `zaakafhandelapp`/`bericht` register/schema, listing messages with
the columns onderwerp, kanaal and datum, plus a detail page
(route `/berichten/:id`).

## Requirements

### Requirement: Messages list view renders

Navigating to `/berichten` SHALL render the messages index page within
the app shell.

#### Scenario: Messages list view renders

- **WHEN** a user navigates to `/berichten`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp` on the berichten route

#### Scenario: Messages navigation entry is reachable

- **WHEN** the app shell is open
- **THEN** the app-navigation sidebar exposes a Messages entry
