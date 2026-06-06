# klanten

## Purpose

Klanten (customers) holds the persons and organisations a case relates
to. The manifest exposes a `klant` index page (route `/klanten`) backed
by the `zaakafhandelapp`/`klant` register/schema, listing customers with
the columns naam, email, telefoon and type, plus a `klant` detail page
(route `/klanten/:id`).

## Requirements

### Requirement: Customers list view renders

Navigating to `/klanten` SHALL render the customers index page within
the app shell.

#### Scenario: Customers list view renders

- **WHEN** a user navigates to `/klanten`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp` on the klanten route

#### Scenario: Customers navigation entry is reachable

- **WHEN** the app shell is open
- **THEN** the app-navigation sidebar exposes a Customers entry

### Requirement: Customer detail shell renders

The customer detail page SHALL render the detail shell so a case handler
can inspect a single customer.

#### Scenario: Customer detail route resolves to the detail shell

- **WHEN** a user navigates to a customer detail route under `/klanten/`
- **THEN** the app-content area renders the detail shell
- **AND** the app does not crash when the underlying record cannot be loaded
