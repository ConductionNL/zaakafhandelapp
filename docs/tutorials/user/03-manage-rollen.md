---
sidebar_position: 3
title: Assign roles to a zaak
description: Add behandelaar, initiator and other rollen to a zaak so the right people are reachable and accountable.
---

# Assign roles to a zaak

ZGW models the parties involved in a case as *rollen* (roles). Each rol links a klant or medewerker to a zaak with a specific responsibility — *behandelaar*, *initiator*, *adviseur*, *belanghebbende*, *beslisser*. This walkthrough wires up the rollen on a freshly created case.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end the zaak has at least a *behandelaar* (the case handler) and an *initiator* (the citizen or organisation that started the case), so the work queue, contact moments and notifications all know who they are about.

## Prerequisites

- A zaak in *Intake* or *In behandeling* status — see [Register a new zaak](./02-create-zaak.md).
- The relevant **klant** (citizen / organisation) and **medewerker** (staff) records exist, or permission to create them inline from the dialog.

## Steps

1. Open **Rollen** from the navigation. The list shows every rol across every zaak — it is a flat view; filter by zaak number to focus.

   ![Rollen list view](/screenshots/tutorials/user/03-manage-rollen-01.png)

2. Click **Add Item** to open the new-rol dialog. Pick the zaak, the rol type (e.g. *Behandelaar*), and the party — a medewerker for staff roles, a klant for citizen roles.

   ![New rol dialog](/screenshots/tutorials/user/03-manage-rollen-02.png)

3. Save. The Rollen list updates and the same rol appears on the zaak's *Rollen* tab. Repeat for each role the case needs — at minimum *behandelaar* + *initiator*; many cases also carry an *adviseur* or *beslisser*.

   ![Rollen list after adding](/screenshots/tutorials/user/03-manage-rollen-03.png)

4. Open **Klanten** to confirm the citizen record on the *Initiator* rol is correct — name, BSN/KVK, contact details. If it is wrong, fix it here once and every rol that references the klant follows.

   ![Klanten list](/screenshots/tutorials/user/03-manage-rollen-04.png)

5. Open **Medewerkers** to confirm the staff record on the *Behandelaar* rol — name, email, the Nextcloud account it is linked to. The behandelaar is who tasks default to and who the work queue treats as the case owner.

   ![Medewerkers list](/screenshots/tutorials/user/03-manage-rollen-05.png)

## Verification

The zaak's *Rollen* tab lists each party with their role type. The dashboard's *Open zaken* widget counts this case under the assigned behandelaar's queue, and notifications about it route to that behandelaar.

## Common issues

| Symptom | Fix |
|---|---|
| The **Rol type** dropdown lacks the role you need | The zaaktype constrains which role types are allowed — an admin extends the zaaktype's `roltypen` list. |
| The **Klant / Medewerker** dropdown is empty | No matching record exists yet. Create one from **Klanten** or **Medewerkers** first, then come back. |
| Saved rol does not appear on the zaak detail page | The zaak ID was not selected — re-open the rol and pick the correct zaak. |

## Reference

- [Track status and result](./04-track-status.md) — once rollen are set, work the status workflow.
- [Features → Roles (Rollen)](../../features/README.md#case-management) — the ZGW role-type taxonomy.
