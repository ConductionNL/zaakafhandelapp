# contactmomenten

## Purpose

Contactmomenten (contact moments) records interactions with a customer
across channels. The manifest exposes a `contactmoment` index page
(route `/contactmomenten`) backed by the
`zaakafhandelapp`/`contactmoment` register/schema, listing contact
moments with the columns onderwerp, kanaal and datum, plus a detail page
(route `/contactmomenten/:id`).

## Requirements

### Requirement: Contact moments list view renders

Navigating to `/contactmomenten` SHALL render the contact moments index
page within the app shell.

#### Scenario: Contact moments list view renders

- **WHEN** a user navigates to `/contactmomenten`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp` on the contactmomenten route

#### Scenario: Contact moments navigation entry is reachable

- **WHEN** the app shell is open
- **THEN** the app-navigation sidebar exposes a Contact moments entry
