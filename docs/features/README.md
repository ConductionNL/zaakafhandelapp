# Zaak Afhandel App Features

> **Deprecation notice:** The Zaak Afhandel App is no longer actively developed. Its functionality has been superseded by [Procest](https://procest.app) (process and case management) and [Pipelinq](https://pipelinq.app) (CRM and pipeline management). This documentation is preserved for reference.

Zaak Afhandel App (ZAA) brings structured case handling (*zaakafhandeling*) into Nextcloud using Dutch government ZGW (Zaakgericht Werken) terminology and patterns. Case workers manage incoming cases from intake through resolution: assign tasks, exchange messages, log customer interactions, record decisions, and track every status transition — all from within Nextcloud.

## Feature Index

| Feature | Description |
|---------|-------------|
| [Case Management](#case-management) | Full ZGW-aligned case lifecycle from intake to closure |
| [Task Management](#task-management) | Work items assigned to staff or customers |
| [Customer and Staff Management](#customer-and-staff-management) | Citizen and employee records linked to cases |
| [Contact Moments](#contact-moments) | Log all customer interactions per case |
| [Documents](#documents) | Attach and manage case-related files |
| [Decisions and Results](#decisions-and-results) | Record formal decisions and case outcomes |
| [Dashboard and Work Queue](#dashboard-and-work-queue) | Personal work queue with case overview |
| [Search](#search) | Full-text search across cases, tasks, and customers |
| [Notifications](#notifications) | Activity stream integration for case events |

## Case Management

Cases (*zaken*) are the central record in ZAA. Each case follows a defined lifecycle from intake through formal closure.

### Case Lifecycle

| Status | Description |
|--------|-------------|
| Intake | Case received, not yet assigned |
| In behandeling | Assigned and actively being handled |
| Wacht op informatie | Waiting for information from citizen or external party |
| Afgerond | Formally closed with a result |
| Ingetrokken | Withdrawn by the citizen |

### Case Types (ZaakTypen)

Case types (*zaaktypen*) define workflow templates for categories of cases:

- Workflow configuration and allowed status transitions
- Required custom metadata fields (*eigenschappen*)
- Default roles and responsibilities
- Document type requirements

### Case Properties (Eigenschappen)

Custom metadata fields per case type allow organisations to capture domain-specific information beyond the standard ZGW fields. Eigenschappen are typed (string, date, number, boolean) and can be required or optional.

### Roles (Rollen)

Typed relationships between parties and a case:

| Role Type | Description |
|-----------|-------------|
| Behandelaar | Primary case handler |
| Adviseur | Advising party |
| Initiator | Party that initiated the case (often the citizen) |
| Belanghebbende | Interested party |
| Medeaanvrager | Co-applicant |
| Beslisser | Decision maker |
| Klantcontacter | Customer contact person |

**Controller:** `lib/Controller/RollenController.php`
**Service:** `lib/Service/ZGWLogicService.php`

### ZGW Data Standard

All case data conforms to the ZGW (Zaakgericht Werken) standard defined by VNG-Realisatie:

- Cases map to the ZGW Zaken API data model
- Case types map to ZGW ZaakType definitions
- Roles, results, and decisions follow ZGW terminology
- The app can proxy ZGW API calls to an external Zaken API instance

## Task Management

Tasks (*taken*) are work items that can be assigned to a staff member or customer as part of case handling:

- Title, description, and due date
- Assignment to a Nextcloud user or external party
- Status tracking (open, in progress, completed, overdue)
- Link to a parent case
- Priority levels

The **Taken** widget on the Nextcloud Dashboard shows the current user's open tasks at a glance.

**Controller:** `lib/Controller/TakenController.php`
**View:** `src/views/taken/`

## Customer and Staff Management

### Customers (Klanten)

Citizens and organisations involved in cases are registered as **klanten**:

- BSN (for individuals) or KVK number (for organisations)
- Name, address, contact details
- Full interaction history across all cases
- Link to the OpenKlant API for federated customer data

**Controller:** `lib/Controller/KlantenController.php`

### Staff (Medewerkers)

Employees who handle or advise on cases:

- Nextcloud user account linkage
- Department and role information
- Active case and task load
- Availability status

**Controller:** `lib/Controller/MedewerkersController.php`

## Contact Moments

Every customer interaction is logged as a **contactmoment**:

- Interaction type (call, email, walk-in, appointment, web form)
- Date, time, and duration
- Participants (staff member and customer)
- Summary and outcome notes
- Link to the case and any resulting tasks or decisions

The complete interaction history per customer or case is available in a chronological timeline view.

**Controller:** `lib/Controller/ContactMomentenController.php`
**View:** `src/views/contactMomenten/`

## Documents

Case-related files are managed through the **documenten** feature:

- Attach files from Nextcloud Files or upload directly
- Document type classification
- Version tracking
- Link to an external DRC (Documentregistratiecomponent) via ZGW API

Documents are accessible from the case detail view and from the Documents section.

**Controller:** `lib/Controller/DocumentenController.php`
**View:** `src/views/documenten/`

## Decisions and Results

### Decisions (Besluiten)

Formal case decisions are recorded and linked to the case:

- Decision type and reference
- Date issued and effective date
- Responsible decision maker (role)
- Link to supporting documents
- Beroepstermijn (appeal period)

**Controller:** `lib/Controller/BesluitenController.php`

### Results (Resultaten)

The final outcome of a case (*resultaat*) is recorded at case closure:

- Result type from a configured result type list
- Toelichting (explanation)
- Archive classification (archiveringsaanduiding, bewaartermijn)

**Controller:** `lib/Controller/ResultatenController.php`

## Dashboard and Work Queue

The **Dashboard** view is the primary entry point for case workers. It shows:

- Personal work queue: open cases assigned to the current user
- Open tasks with due dates
- Recent contact moments
- Quick-access buttons for creating new cases and tasks

### Nextcloud Dashboard Widgets

ZAA registers multiple widgets in the Nextcloud Dashboard:

| Widget | Description |
|--------|-------------|
| Zaken | Open cases assigned to the current user |
| Taken | Open tasks assigned to the current user |
| OpenZaken | All open cases (for managers) |
| ContactMomenten | Recent customer interactions |
| Organisaties | Active organisations |
| Personen | Key contacts |

**View:** `src/views/dashboard/`

## Search

Full-text search across cases, customers, tasks, and contact moments. Supports:

- Keyword search across all text fields
- Faceted filtering by case type, status, assigned user, and date range
- Optional Elasticsearch backend for large datasets

**Controller:** `lib/Controller/ObjectsController.php`
**View:** `src/views/search/`

## Notifications

ZAA integrates with the Nextcloud Activity system. Case lifecycle events generate notifications:

- Case assigned to you
- New message on your case
- Task approaching due date
- Status changed on a case you are following
- New decision on a case

**Event listeners:** `lib/EventListener/`

## Architecture

### Backend

| Layer | Description |
|-------|-------------|
| Controllers | REST API controllers per entity type (Zaken, Taken, Klanten, etc.) |
| Services | Business logic: `ZGWLogicService`, `CallService`, `ObjectService` |
| ZGW Proxy | `CallService` proxies ZGW API calls to external Zaken API instances |

### Frontend

| Layer | Description |
|-------|-------------|
| Views | Route-level Vue 2 components per entity type |
| Stores | Pinia stores per entity with CRUD operations |
| Entities | Zod-validated entity classes for type safety |
| Modals | CRUD modals for create/edit operations |
| Widgets | Nextcloud Dashboard widget entry points |

## Standards and Compliance

| Standard | Role |
|----------|------|
| ZGW Zaken API (VNG-Realisatie) | Primary data model and API design |
| GEMMA Zaakgericht Werken | Architectural reference |
| WCAG AA | Accessibility requirement |
| EUPL-1.2 | License |

## Data Model

| Entity | Dutch Term | Description |
|--------|-----------|-------------|
| Case | Zaak | Central case record |
| Case Type | ZaakType | Workflow template for a class of cases |
| Task | Taak | Work item linked to a case |
| Customer | Klant | Citizen or organisation in a case |
| Staff | Medewerker | Employee handling the case |
| Role | Rol | Typed relationship between party and case |
| Contact Moment | Contactmoment | Logged customer interaction |
| Message | Bericht | Message within a case |
| Decision | Besluit | Formal case decision |
| Document | Document | File attached to a case |
| Result | Resultaat | Case outcome at closure |
| Case Property | Eigenschap | Custom metadata field on a case |
| Status | Status | Current lifecycle status of a case |
