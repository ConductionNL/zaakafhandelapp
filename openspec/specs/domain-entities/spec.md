---
retrofit: true
---

# Domain Entities

The TypeScript entity classes that model each ZGW resource on the client (bericht,
besluit, contactmoment, document, klanten, medewerkers, resultaat, rol, taak,
zaak, zaakTypen). Each entity normalises a raw API record into a typed object on
construction. Reverse-specified from observed entity behavior.

## Requirements

### REQ-001: Construct a typed entity from a raw record

The system SHALL, on construction, map a raw API record onto the entity's typed
fields, applying defaults for missing fields so downstream code can rely on a
consistent shape.

#### Scenario: Wrapping a raw zaak

- **WHEN** a raw zaak record is passed to the Zaak entity constructor
- **THEN** the entity exposes the record's fields as typed properties with
  defaults applied
