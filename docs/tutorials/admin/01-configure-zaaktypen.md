---
sidebar_position: 1
title: Configure zaaktypen
description: Define case-type templates — allowed statuses, roltypen, besluittypen and eigenschappen — that drive every zaak created from them.
---

# Configure zaaktypen

A *zaaktype* is a workflow template: it tells ZAA what statuses a zaak of this type can move through, which rollen are valid, which eigenschappen (custom metadata) are required, which besluittypen and informatieobjecttypen apply. Every zaak references exactly one zaaktype.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end you have at least one zaaktype configured with a sensible default status workflow (Intake → In behandeling → Afgerond), the required roltypen, and the eigenschappen the work needs — ready for case handlers to register their first zaak.

## Prerequisites

- Admin on the Nextcloud instance.
- OpenRegister installed and the ZAA configuration import run (see [Manage Zaak Afhandel App settings](./03-admin-settings.md)).

## Steps

1. Open **Zaaktypen** from the in-app navigation. On a fresh install the list is empty — every zaaktype is org-specific and admin-managed.

   ![Zaaktypen list view](/screenshots/tutorials/admin/01-configure-zaaktypen-01.png)

2. Click **Add Item**. The dialog asks for *Identificatie* (a stable code), *Omschrijving*, *Doorlooptijd* (statutory turnaround), *Servicenorm*, *Vertrouwelijkheidaanduiding*, and the relations: *Roltypen*, *Statustypen*, *Resultaattypen*, *Besluittypen*, *Informatieobjecttypen*, *Eigenschappen*.

   ![New zaaktype dialog](/screenshots/tutorials/admin/01-configure-zaaktypen-02.png)

3. Add at least three statustypen — *Intake* (volgnummer 1), *In behandeling* (2), *Afgerond* (3). The volgnummer drives the order in which case handlers see them in the status dropdown.

   ![Zaaktype - status workflow](/screenshots/tutorials/admin/01-configure-zaaktypen-03.png)

4. Add the roltypen the process needs — *behandelaar* and *initiator* are the minimum; many processes add *adviseur*, *beslisser*, *belanghebbende*. Each roltype has an `omschrijvingGeneriek` from the ZGW canonical list.

   ![Zaaktype - roltypen](/screenshots/tutorials/admin/01-configure-zaaktypen-04.png)

5. Save. The zaaktype now appears on the **Zaaktypen** list and on the zaaktype dropdown when a case handler creates a new zaak (see [Register a new zaak](../user/02-create-zaak.md)).

   ![Zaaktype - resultaten](/screenshots/tutorials/admin/01-configure-zaaktypen-05.png)

## Verification

The zaaktype shows up on the Zaaktypen list with the correct counts of statustypen / roltypen / resultaattypen. A new zaak created from it offers the configured statuses in the right order; the rollen dialog offers the configured roltypen; the besluit dialog offers the configured besluittypen.

## Common issues

| Symptom | Fix |
|---|---|
| New zaken from this type only allow forward status transitions | That is by design — backward transitions need an explicit `mogelijkeStatustypen` configuration. Add the previous status as a target on each forward status's `mogelijkeStatustypen` list. |
| Saved zaaktype, but the **Zaaktype** dropdown still does not show it | Browser cache — hard-reload (Ctrl-Shift-R). Vue Router stores a stale autocomplete index for ~1 minute. |
| Servicenorm field stays unhighlighted on overdue zaken | Servicenorm tracking is opt-in per zaaktype — set both `doorlooptijd` and `servicenorm` non-zero, otherwise the SLA widget treats the type as unbounded. |

## Reference

- [Register a new zaak](../user/02-create-zaak.md) — the case-handler side of the workflow template.
- [Manage medewerkers](./02-manage-medewerkers.md) — once zaaktypen exist, the next admin step.
