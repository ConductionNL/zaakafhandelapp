# dashboard

## Purpose

The zaakafhandelapp dashboard is the default landing view of the
manifest-driven app shell. It is a manifest `dashboard`-type page
(route `/`) that renders a grid of `stats-block` KPI widgets summarising
the case-handling domain (cases, tasks, contact moments, customers).
It is the first view a case handler sees after opening the app from the
Nextcloud app menu.

## Requirements

### Requirement: Dashboard is the default app view

Opening the app at `/apps/zaakafhandelapp/` SHALL mount the Vue SPA and
render the dashboard page as the default route.

#### Scenario: User opens the app

- **WHEN** a user navigates to `/apps/zaakafhandelapp/`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp`

#### Scenario: Dashboard heading renders

- **WHEN** the dashboard page is open
- **THEN** the "Dashboard" page title is visible

### Requirement: Dashboard shows the case-handling navigation

The manifest menu SHALL expose the primary case-handling views in the
app-navigation sidebar so the user can reach cases, tasks and customers.

#### Scenario: Sidebar shows the primary views

- **WHEN** the dashboard is open
- **THEN** the app-navigation sidebar is visible
- **AND** it contains entries for Cases, Tasks and Customers

### Requirement: Dashboard renders KPI stat widgets

The dashboard SHALL render the configured `stats-block` KPI widgets
(open cases, open tasks, active cases, contact moments) even when their
counts resolve to zero.

#### Scenario: KPI widget area renders

- **WHEN** the dashboard is open
- **THEN** the app-content area renders the KPI widget grid
- **AND** the dashboard does not crash when counts are zero
