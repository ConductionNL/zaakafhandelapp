---
retrofit: true
---

# App Configuration

@e2e exclude pure-backend REST+controller spec — scenarios covered by PHPUnit/Newman, not Playwright UI tests

Administrative configuration, settings, dashboard and current-user surfaces of the
case-handling app. Covers reading and persisting the ZGW source connection config
(DRC/ORC/ZRC/ZTC/BRC + klanten/elastic/mongodb + organisation identifiers),
per-object-type register/schema/source settings, the dashboard KPI endpoints, and
the current-user profile lookup. Reverse-specified from observed controller
behavior.

## Requirements

### REQ-001: Read and persist ZGW connection configuration

The system SHALL return the stored configuration values for every known
configuration key (filling defaults for unset keys) and SHALL persist posted
configuration values back to app config, echoing the stored result.

#### Scenario: Reading configuration

- **WHEN** an admin reads the configuration
- **THEN** the system returns each known key with its stored or default value

#### Scenario: Saving configuration

- **WHEN** an admin posts configuration values
- **THEN** the system stores each value and returns the persisted set

### REQ-002: Read and persist object-type settings and available registers

The system SHALL return the available object types, whether OpenRegister is
present, the available registers, and the per-object-type source/schema/register
settings (with defaults), and SHALL persist posted settings — returning an error
response with HTTP 500 on failure.

#### Scenario: Reading settings with OpenRegister present

- **WHEN** an admin reads settings and OpenRegister is installed
- **THEN** the system returns the available registers and object-type settings

### REQ-003: Serve dashboard data

The system SHALL provide the dashboard endpoints (page render plus list/read/
create/update/delete of dashboard objects) returning JSON.

#### Scenario: Loading the dashboard

- **WHEN** a client requests the dashboard index
- **THEN** the system returns the dashboard result set as JSON

### REQ-004: Return the current user profile

The system SHALL return the authenticated user's profile (identity, email,
quota, capabilities and related medewerker reference), returning an error
response with HTTP 500 on failure.

#### Scenario: Fetching the current user

- **WHEN** an authenticated client requests its own profile
- **THEN** the system returns the user details as JSON
