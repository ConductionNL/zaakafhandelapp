# search

## Purpose

Search is a custom manifest page (route `/zoeken`, menu entry "Search")
rendered by the `SearchView` custom component. In its current state it
is a placeholder surface that renders a "Search" heading and a
no-results message; cross-store search lands in a follow-up. The spec
captures the placeholder shell that ships today.

## Requirements

### Requirement: Search view renders

Navigating to the search route SHALL render the search page within the
app shell with its heading.

#### Scenario: Search page renders its heading

- **WHEN** a user navigates to the `/zoeken` search route
- **THEN** the app-content area renders
- **AND** a "Search" heading is visible
