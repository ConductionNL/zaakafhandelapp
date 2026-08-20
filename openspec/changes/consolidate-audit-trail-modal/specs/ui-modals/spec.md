# UI — Modals & Dialogs — Audit-Trail Entry Delta

**Spec refs**: `ui-modals`, ADR-004 (frontend — NC CSS variables, no hardcoded colors), ADR-017 (component composition)

## ADDED Requirements

### Requirement: View a resource's audit-trail entry (REQ-006)

The system SHALL provide a single shared modal component for viewing one
audit-trail entry (id, action, user, session, IP address, created timestamp,
and a before/after table of changed fields when present), reusable across
every ZGW resource that exposes a "view audit-trail entry" action (zaken,
klanten, taken, berichten). The component MUST source its displayed entry
from a prop, not a hardcoded store import, so a single implementation serves
every resource. The modal MUST use Nextcloud CSS variables for all colors
(no hardcoded hex values).

#### Scenario: Viewing a zaak's audit-trail entry

- **GIVEN** a user opens the audit-trail entry modal from a zaak
- **WHEN** the modal renders
- **THEN** it shows the entry's id, action, user, session, IP address, and
  created timestamp, sourced from the zaak store's `auditTrailItem`

#### Scenario: Viewing a changed-fields table

- **GIVEN** an audit-trail entry has a `changed` map of field diffs
- **WHEN** the modal renders that entry
- **THEN** it shows a table of field / old value / new value rows, formatting
  `null`/`undefined` as "N/A", objects as pretty-printed JSON, and booleans as
  "True"/"False"

#### Scenario: Closing the modal resets the source store

- **WHEN** the user closes the audit-trail entry modal
- **THEN** the modal closes and the originating resource's `auditTrailItem` is
  cleared on that resource's store

#### Scenario: No hardcoded colors

- **WHEN** the modal is inspected for hardcoded colors
- **THEN** every color value (including the entry's border divider) resolves
  to a Nextcloud CSS variable, not a fixed hex value
