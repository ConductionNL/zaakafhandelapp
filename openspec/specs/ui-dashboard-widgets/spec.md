---
retrofit: true
---

# UI — Dashboard Widgets

@e2e exclude SPA does not mount on current env (white-screen, #content empty after JS load) — tracked in ConductionNL/zaakafhandelapp#264; re-enable once mount is fixed

The Nextcloud dashboard widgets that surface case-handling data on the home
dashboard: open zaken, taken, contactmomenten, personen, organisaties and a
general zaken widget. Each widget fetches a scoped set of items, presents them
compactly, derives per-item icons and menus, and links into the full views.
Reverse-specified from observed component behavior.

## Requirements

### REQ-001: Fetch the widget's items

The system SHALL fetch the scoped item set for the widget (e.g. open zaken, the
current user's taken, recent contactmomenten, personen, organisaties) and expose
them as the widget's items, optionally resolving the current user first.

#### Scenario: Loading the open-zaken widget

- **WHEN** the open zaken widget mounts
- **THEN** it fetches the open zaken and presents them as items

### REQ-002: Search and filter widget items

The system SHALL filter the widget's items by the user's search input.

#### Scenario: Searching within a widget

- **WHEN** the user types in the widget search
- **THEN** the widget filters its items

### REQ-003: Derive per-item presentation

The system SHALL derive each item's icon, label and context menu from the item
data, including channel-specific icons for contactmomenten (phone/email/agent/
mailbox).

#### Scenario: Rendering a contactmoment item

- **WHEN** the contactmomenten widget renders an item
- **THEN** it derives the channel icon from the contactmoment

### REQ-004: Open and act on a widget item

The system SHALL open a modal or navigate to the resource when the user activates
an item, and support per-item actions (edit, show, close, mark handled), opening
and closing the relevant modal.

#### Scenario: Showing a widget item

- **WHEN** the user activates a widget item
- **THEN** the widget opens the item detail or navigates to it

### REQ-005: Manage widget modals and related lookups

The system SHALL open/close the widget's own modals and fetch related lookups
(e.g. a person's zaken/taken/contactmomenten, or klant search results) when
required.

#### Scenario: Opening klant search from the personen widget

- **WHEN** the user opens klant search in the personen widget
- **THEN** the widget shows the klant search modal and fetches results
