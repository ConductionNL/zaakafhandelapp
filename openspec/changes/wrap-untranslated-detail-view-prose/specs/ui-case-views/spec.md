# ui-case-views — Translatable Detail Prose Delta

**Spec refs**: `ui-case-views`, hydra ADR-007 (i18n — English source, `t()`
for all user-facing strings)

## MODIFIED Requirements

### Requirement: Select and expand a resource for detail (REQ-002)

The system SHALL set the active resource item when the user selects a row and
expose its detail, including expanding/collapsing nested rows where the view
supports it. Every user-facing label, field heading, empty-state message, and
tooltip rendered in the detail view MUST be sourced through
`t('zaakafhandelapp', '<English key>')` (or `n()` for pluralised text) — no
literal Dutch or other non-English prose may be hardcoded directly into the
template.

#### Scenario: Selecting a zaak

- **WHEN** the user clicks a zaak in the list
- **THEN** the view sets it as the active item and shows its detail

#### Scenario: Detail prose follows the selected locale

- **GIVEN** the user's Nextcloud language is set to a non-Dutch supported
  locale (e.g. English)
- **WHEN** the user opens a zaak's detail view
- **THEN** every label, field heading, empty-state message and tooltip in
  the detail view renders in that locale, not unconditional Dutch
