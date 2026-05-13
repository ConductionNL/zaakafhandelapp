---
sidebar_position: 2
title: Manage medewerkers
description: Register staff records, link them to Nextcloud accounts, and define who can act as behandelaar.
---

# Manage medewerkers

A *medewerker* is the ZAA-side record for a member of staff — name, function, contact details, and the Nextcloud account they sign in with. Medewerker records drive the *behandelaar* and *adviseur* rollen, the work-queue assignment, and the audit trail of who made each status change.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end every active case handler has a medewerker record linked to their Nextcloud account, so the rollen dialog can offer them, the taken widget can scope to them, and the audit trail can name them.

## Prerequisites

- Admin on the Nextcloud instance.
- The Nextcloud user accounts already exist (or will be created in parallel from **Users → New user**).

## Steps

1. Open **Medewerkers** from the in-app navigation. On a fresh install the list is empty.

   ![Medewerkers list view](/screenshots/tutorials/admin/02-manage-medewerkers-01.png)

2. Click **Add Item**. The dialog asks for *Naam*, *Functie*, *Email*, *Telefoon*, and the *Nextcloud account* — the userid that should be linked.

   ![New medewerker dialog](/screenshots/tutorials/admin/02-manage-medewerkers-02.png)

3. Save. The medewerker appears in the list. Repeat for every active case handler. Inactive staff stay in the list but the dropdown can be filtered to active-only.

   ![Medewerkers list after adding](/screenshots/tutorials/admin/02-manage-medewerkers-03.png)

4. Open **Rollen** to confirm the medewerkers you just registered show up as candidates for the *behandelaar* / *adviseur* rol types. Existing rollen are not retro-linked — only new rollen pick up the new medewerkers.

   ![Rollen list](/screenshots/tutorials/admin/02-manage-medewerkers-04.png)

5. Open **Audit Trail** to confirm the medewerker registrations are themselves logged. Every change in ZAA — create, update, delete — lands here with the actor's userid and timestamp.

   ![Audit Trail](/screenshots/tutorials/admin/02-manage-medewerkers-05.png)

## Verification

The Medewerkers list shows every active case handler with their Nextcloud account linked. The Rollen dialog's *Medewerker* dropdown contains them all. The Audit Trail shows a *create* row per medewerker, attributed to the admin who registered them.

## Common issues

| Symptom | Fix |
|---|---|
| **Nextcloud account** dropdown is empty | The Nextcloud user accounts do not exist — create them under Settings → Users first. |
| New medewerker is not in the rollen dropdown | The rollen dialog caches its candidate list for ~1 minute. Hard-reload, or wait. |
| Cannot delete a medewerker | They are referenced by historical rollen / taken / status changes. Either keep them (set them to *Inactief*) or run the cascade-delete from OpenRegister directly — the latter destroys audit history, so don't. |

## Reference

- [Assign roles to a zaak](../user/03-manage-rollen.md) — what these medewerker records flow into.
- [Manage settings](./03-admin-settings.md) — the app-wide settings page.
