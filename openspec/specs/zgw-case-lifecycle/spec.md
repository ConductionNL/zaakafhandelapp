---
retrofit: true
---

# ZGW Case Lifecycle

@e2e exclude pure-backend PHP service logic spec — scenarios covered by PHPUnit, not Playwright UI tests

Business logic that enforces the ZGW lifecycle and integrity rules as objects are
created, related, transitioned and archived. Covers the cascade relations between
zaken, besluiten and informatieobjecten; status-driven case open/close/reopen
transitions; archive-date calculation; and the validation rules that gate
archiving and decision linking. Reverse-specified from observed service behavior.

## Requirements

### REQ-001: Maintain ZGW cascade relations between objects

The system SHALL, when ZGW objects are created or deleted, maintain the derived
relations the standard requires: linking objectinformatieobjecten to their zaak or
besluit, creating and deleting zaakbesluiten for a besluit, and creating/deleting
zaaktype-informatieobjecttype links — including cleaning up the related objects
when a parent is deleted.

#### Scenario: Creating a besluit links it to its zaak

- **WHEN** a besluit object is created
- **THEN** the system creates the corresponding zaakbesluit relation

#### Scenario: Deleting a besluit cleans up relations

- **WHEN** a besluit is deleted
- **THEN** the system removes the dependent objectinformatieobject / zaakbesluit
  relations

### REQ-002: Transition a case open, closed or reopened on status change

The system SHALL, when a zaak status changes, close the zaak when the status is an
eind-status (setting the close-relevant fields), reopen it when moving away from an
eind-status, and recognise whether a given status array represents an end status.

#### Scenario: Closing a case on end status

- **WHEN** a status that is an eind-status is set on a zaak
- **THEN** the system marks the zaak closed and records the closing data

#### Scenario: Reopening a case

- **WHEN** a zaak that was closed receives a non-eind status
- **THEN** the system reopens the zaak

### REQ-003: Apply confidentiality and deletion lifecycle actions

The system SHALL apply a zaak's vertrouwelijkheidaanduiding (confidentiality
level) across its related objects, and delete a zaak together with its dependent
records.

#### Scenario: Cascading confidentiality

- **WHEN** a zaak's confidentiality level is set
- **THEN** the system propagates it to the related objects

### REQ-004: Calculate the archive date

The system SHALL calculate the archiefactiedatum for a zaak from its
resultaat/archiefnominatie and the configured retention term.

#### Scenario: Computing the archive date

- **WHEN** archive parameters are provided for a zaak
- **THEN** the system returns the calculated archive date

### REQ-005: Validate archiving and decision-linking prerequisites

The system SHALL enforce ZGW validation rules: that a zaak has the required
producten-of-diensten, gegevensgroepen and archive prerequisites before archiving,
that relevante-andere-zaken references are consistent, and that a
besluitinformatieobject is valid before it is linked. Failed checks raise an
error rather than persisting an invalid state.

#### Scenario: Blocking an archive when prerequisites are missing

- **WHEN** a zaak is checked for archiving without the required gegevensgroepen
- **THEN** the system raises a validation error
