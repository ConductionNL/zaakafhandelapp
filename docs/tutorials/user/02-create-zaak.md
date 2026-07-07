---
sidebar_position: 2
title: Register a new zaak
description: Open the Zaken list, create a new case from a zaaktype template, and fill in the standard ZGW fields.
---

# Register a new zaak

A *zaak* (case) is the central record in ZAA. Every interaction with a citizen, every assigned task, every recorded decision hangs off a zaak. This walkthrough creates one from scratch.

:::warning Deprecated

ZAA is no longer actively developed. See [the deprecation notice](../../intro.md) and consider migrating to [Procest](https://procest.app).

:::

## Goal

By the end you will have created a new zaak from a zaaktype template, filled in the mandatory ZGW fields, and confirmed the case shows up in the Zaken list ready to be worked on.

## Prerequisites

- At least one **zaaktype** configured by an admin — see [Configure zaaktypen](../admin/01-configure-zaaktypen.md). Without a zaaktype the create dialog has nothing to base the new case on.
- A **klant** record for the citizen or organisation the zaak is about (or permission to create one inline from the dialog).
- The first-launch checks pass — see [Open Zaak Afhandel App for the first time](./01-first-launch.md).

## Steps

1. Open **Zaken** from the in-app navigation. The Zaken list view appears with the **Add Item** button in the top right.

   ![Zaken list view](/screenshots/tutorials/user/02-create-zaak-01.png)

2. Click **Add Item**. The new-zaak dialog opens with the standard ZGW fields — *Zaaktype*, *Omschrijving*, *Toelichting*, *Registratiedatum*, *Startdatum* — plus any *eigenschappen* (custom metadata) the chosen zaaktype defines.

   ![New zaak dialog](/screenshots/tutorials/user/02-create-zaak-02.png)

3. Pick a zaaktype, then fill in the description and start date. The form validates required fields inline — empty mandatory fields highlight in red and the **Save** button stays disabled until they are filled.

   ![Zaken list after saving](/screenshots/tutorials/user/02-create-zaak-03.png)

4. The available zaaktypen list (under **Zaaktypen** in the navigation) is what the dropdown above is reading from. It is admin-managed; if a template is missing, point your admin at [Configure zaaktypen](../admin/01-configure-zaaktypen.md).

   ![Zaaktypen list](/screenshots/tutorials/user/02-create-zaak-04.png)

5. Save. The new zaak appears in the Zaken list with status *Intake* (or the start status configured on the zaaktype). From here you assign roles ([Assign roles to a zaak](./03-manage-rollen.md)), advance status ([Track status and result](./04-track-status.md)), and add work ([Add a taak to a zaak](./05-add-taak.md)).

   ![Zaaktype detail](/screenshots/tutorials/user/02-create-zaak-05.png)

## Verification

The new zaak shows up at the top of the Zaken list with the description you entered. Clicking it opens the detail page and shows the configured status workflow, the eigenschappen tab populated from the zaaktype, and an empty Rollen/Taken/Documenten tab — ready to be filled in.

## Common issues

| Symptom | Fix |
|---|---|
| The **Zaaktype** dropdown is empty | No zaaktypen are configured. Ask an admin to add at least one (see [Configure zaaktypen](../admin/01-configure-zaaktypen.md)). |
| **Save** stays greyed out | A required field is empty — the form highlights the offending row in red. Often it is a missing eigenschap that the zaaktype marks as required. |
| Dialog body is blank | The OpenRegister schema for `zaak` is not mapped — admin re-runs the configuration import. |

## Reference

- [Assign roles to a zaak](./03-manage-rollen.md) — the next step after creation.
- [Configure zaaktypen](../admin/01-configure-zaaktypen.md) — where the templates that drive this dialog are managed.
- [Features → Case Management](../../features/README.md) — the ZGW lifecycle overview.
