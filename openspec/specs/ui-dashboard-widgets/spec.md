---
retrofit: true
---

# UI — Dashboard Widgets

## Purpose

The Nextcloud dashboard widgets that surface case-handling data on the home
dashboard: open zaken, taken, contactmomenten, personen, organisaties and a
general zaken widget. Each widget fetches a scoped set of items, presents them
compactly, derives per-item icons and menus, and links into the full views.
Reverse-specified from observed component behavior.
## Requirements
### Requirement: Fetch the widget's items (REQ-001)

The system SHALL fetch the scoped item set for the widget (e.g. open zaken, the
current user's taken, recent contactmomenten, personen, organisaties) and expose
them as the widget's items, optionally resolving the current user first.

#### Scenario: Loading the open-zaken widget

- **WHEN** the open zaken widget mounts
- **THEN** it fetches the open zaken and presents them as items

### Requirement: Search and filter widget items (REQ-002)

The system SHALL filter the widget's items by the user's search input.

#### Scenario: Searching within a widget

- **WHEN** the user types in the widget search
- **THEN** the widget filters its items

### Requirement: Derive per-item presentation (REQ-003)

The system SHALL derive each item's icon, label and context menu from the item
data, including channel-specific icons for contactmomenten (phone/email/agent/
mailbox).

#### Scenario: Rendering a contactmoment item

- **WHEN** the contactmomenten widget renders an item
- **THEN** it derives the channel icon from the contactmoment

### Requirement: Open and act on a widget item (REQ-004)

The system SHALL open a modal or navigate to the resource when the user activates
an item, and support per-item actions (edit, show, close, mark handled), opening
and closing the relevant modal.

#### Scenario: Showing a widget item

- **WHEN** the user activates a widget item
- **THEN** the widget opens the item detail or navigates to it

### Requirement: Manage widget modals and related lookups (REQ-005)

The system SHALL open/close the widget's own modals and fetch related lookups
(e.g. a person's zaken/taken/contactmomenten, or klant search results) when
required.

#### Scenario: Opening klant search from the personen widget

- **WHEN** the user opens klant search in the personen widget
- **THEN** the widget shows the klant search modal and fetches results

### Requirement: Surface deadline urgency in the open-zaken widget (REQ-006)

The system SHALL surface deadline urgency in the open-zaken dashboard widget:
each item shows its urgency indicator (per `zaak-termijn-monitoring` REQ-003)
and deadline date, items are ordered most-urgent-first (`verlopen`, then
`bijna-verlopen` by nearest deadline, then the rest), and the widget header
shows the count of overdue zaken when there are any. Activating an item keeps
the existing behaviour (REQ-004) of opening the zaak.

#### Scenario: Overdue zaken float to the top of the widget

- **GIVEN** the open zaken include one overdue zaak and several without
  urgency
- **WHEN** the open-zaken widget renders
- **THEN** the overdue zaak is listed first with an overdue indicator and the
  widget signals 1 overdue zaak

#### Scenario: No overdue zaken, no alarm

- **GIVEN** no open zaak is overdue
- **WHEN** the widget renders
- **THEN** no overdue count is shown and items render as today

