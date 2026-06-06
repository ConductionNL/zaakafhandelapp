# taken

## Purpose

Taken (tasks) lets case handlers track work items. The manifest exposes
a `taak` index page (route `/taken`) backed by the
`zaakafhandelapp`/`taak` register/schema, listing tasks with the columns
title, status, priority and dueDate, plus a `taak` detail page
(route `/taken/:id`).

## Requirements

### Requirement: Tasks list view renders

Navigating to `/taken` SHALL render the tasks index page within the app
shell.

#### Scenario: Tasks list view renders

- **WHEN** a user navigates to `/taken`
- **THEN** the app-content area renders
- **AND** the URL is within `/apps/zaakafhandelapp` on the taken route

#### Scenario: Tasks navigation entry is reachable

- **WHEN** the app shell is open
- **THEN** the app-navigation sidebar exposes a Tasks entry

### Requirement: Create-task dialog opens from the list

The tasks index SHALL let the user open a create dialog to add a new
task.

#### Scenario: Create task dialog opens

- **WHEN** the user activates the add/create control on the tasks list
- **THEN** a dialog opens with task input fields
- **AND** the user can dismiss the dialog and return to the list
