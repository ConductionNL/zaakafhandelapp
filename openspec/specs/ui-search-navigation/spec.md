---
retrofit: true
---

# UI — Search, Navigation & Utilities

Cross-cutting frontend surfaces: the search sidebar (searching klanten,
personen and organisaties with debounced input), the configuration navigation
panel, the app permissions surface, and shared presentational utilities (theme
resolution, ISO date normalisation, and the MDI icon path providers used across
the app). Reverse-specified from observed component behavior.

## Requirements

### REQ-001: Search across klanten, personen and organisaties

The system SHALL search klanten, personen and organisaties from the sidebar,
debouncing user input, exposing the active result tab, and deriving display
name/subname for each result.

#### Scenario: Searching from the sidebar

- **WHEN** the user types a query in the search sidebar
- **THEN** the system debounces the input, runs the searches, and presents the
  results under the active tab

### REQ-002: Load and save app configuration from the nav panel

The system SHALL fetch the current configuration into the navigation panel and
persist edited configuration back through the configuration endpoint.

#### Scenario: Saving configuration from the nav panel

- **WHEN** the user saves configuration in the nav panel
- **THEN** the system posts the configuration and reflects the result

### REQ-003: Expose the app permission surface

The system SHALL compute the permission flags the app uses to gate UI actions.

#### Scenario: Resolving permissions

- **WHEN** the app initialises
- **THEN** it exposes the current permission flags to its child views

### REQ-004: Resolve theme and normalise dates

The system SHALL resolve the active Nextcloud theme for presentational use, and
normalise arbitrary date input into a valid ISO string (returning a safe value
for invalid input).

#### Scenario: Normalising a date

- **WHEN** a component needs an ISO date from raw input
- **THEN** the utility returns a valid ISO string or a safe fallback

### REQ-005: Provide MDI icon path data

The system SHALL expose the SVG path data for the app's MDI glyphs
(briefcase-account, calendar-check, calendar-month, card-account-phone, pencil,
progress-close) for use by icon components.

#### Scenario: Rendering an icon

- **WHEN** a component renders an MDI icon
- **THEN** the icon provider returns the glyph's SVG path data
