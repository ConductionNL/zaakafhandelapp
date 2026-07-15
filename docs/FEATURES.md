---
sidebar_position: 2
---

# Features

:::warning Deprecated

The Zaak Afhandel App is no longer actively developed. Its functionality has been superseded by:

- **[Procest](https://procest.app)** — Process and case management for Nextcloud
- **[Pipelinq](https://pipelinq.app)** — CRM and pipeline management for Nextcloud

We recommend migrating to one of these apps. This documentation is preserved for reference.

:::

## Overview

Zaak Afhandel App (ZAA) is a case handling application for Nextcloud that implements Dutch government ZGW (Zaakgericht Werken) terminology and patterns. It exposes a ZGW (ZRC/ZTC/DRC/BRC) REST API surface, built on OpenRegister for storage, search, RBAC and audit.

## Core Features (Archived)

### Case Management (ZGW)
Structured case handling using the ZGW framework. Manage case types, custom metadata (eigenschappen), and full case lifecycle from creation to closure, including opschorting and verlenging (suspend/extend) on a running case.

### ZGW REST API
Zaken, taken, besluiten and documenten are reachable through a ZGW (ZRC/ZTC/DRC/BRC) REST API surface, so other ZGW-speaking systems can integrate directly.

### Role Assignment
Assign roles (rollen) to cases and participants. Track who is responsible for each aspect of a case.

### Task Management
Create and assign tasks (taken) related to cases. Track task completion and deadlines.

### Customer Interactions
Record all contact moments (contactmomenten) with citizens and organizations. Full audit trail of case communications.

### Decision Logging
Log decisions (besluiten) and their rationale. Maintain a complete record of all case decisions for accountability.

### Status Transitions
Manage case status through defined workflows. Track progress from intake through to resolution.
