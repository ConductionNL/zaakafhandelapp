---
sidebar_position: 5
title: Add a taak to a zaak
description: Create a task, link it to a zaak, and assign it to a medewerker or klant.
---

# Add a taak to a zaak

Most case-handling work shows up as *taken* (tasks) — checklist items the behandelaar needs to finish, or work the citizen needs to deliver. Each taak hangs off a zaak so the audit trail is intact.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end you have created a taak, linked it to a zaak, assigned it to a medewerker or klant, given it a due date, and confirmed it shows up in the assignee's dashboard widget.

## Prerequisites

- A zaak in *In behandeling* status — see [Track status and result](./04-track-status.md).
- A medewerker (or klant) to assign the taak to.

## Steps

1. Open **Taken** from the navigation. The list shows every taak across every zaak — like Statussen this is the cross-cutting view.

   ![Taken list view](/screenshots/tutorials/user/05-add-taak-01.png)

2. Click **Add Item**. The new-taak dialog asks for *Zaak*, *Titel*, *Toelichting*, *Toegewezen aan*, *Deadline*, and *Prioriteit*. Pick the zaak first — the rest of the form scopes itself to that case.

   ![New taak dialog](/screenshots/tutorials/user/05-add-taak-02.png)

3. Save. The taak appears in the Taken list with status *Open* and counts against the assignee's open-taken total.

   ![Taken list after adding](/screenshots/tutorials/user/05-add-taak-03.png)

4. Open the taak detail page. From here update the status (*Open* → *In behandeling* → *Afgerond*), attach files, or add a comment. The audit trail captures each change.

   ![Taak detail page](/screenshots/tutorials/user/05-add-taak-04.png)

5. Return to the dashboard. The **Taken** widget shows the current user's open taken at a glance — both ones they are assigned to and ones they created. Click any row to jump to the taak.

   ![Dashboard with Taken widget](/screenshots/tutorials/user/05-add-taak-05.png)

## Verification

The taak shows up in the *Open* state on both the Taken list and the assignee's dashboard widget. Closing it (status *Afgerond*) removes it from the widget but keeps it on the parent zaak's *Taken* tab for the audit trail.

## Common issues

| Symptom | Fix |
|---|---|
| The **Zaak** dropdown is empty | No zaken exist yet — create one (see [Register a new zaak](./02-create-zaak.md)). |
| **Toegewezen aan** dropdown lacks the colleague you want | The medewerker record is missing — admin adds it (see [Manage medewerkers](../admin/02-manage-medewerkers.md)). |
| Taak appears in Taken but not on the zaak's detail page | The taak was saved without a zaak link — open it and set the **Zaak** field, or delete and recreate. |

## Reference

- [Record a contactmoment](./06-record-contactmoment.md) — taken often follow up on a call or e-mail.
- [Features → Task Management](../../features/README.md#task-management) — feature overview and the dashboard widget.
