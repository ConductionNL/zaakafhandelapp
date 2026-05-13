---
sidebar_position: 8
title: Log a besluit
description: Record a formal decision against a zaak — the besluit, its motivation, and the besluitstuk that documents it.
---

# Log a besluit

When a case ends in a formal decision — a permit granted, an objection rejected, a subsidy approved — that decision is recorded as a *besluit*. ZGW separates the decision (besluit) from the case outcome (resultaat) and from the document that captures the legal reasoning (*besluitstuk*).

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end you have logged a besluit against the zaak with its *besluittype*, decision date, ingangsdatum (effective date), motivation, and a linked besluitstuk document — so the case can be closed with full audit trail.

## Prerequisites

- A zaak in *In behandeling* status with all required taken closed.
- A *besluittype* configured on the parent zaaktype — the besluit dialog reads this for the dropdown.
- The signed besluitstuk file (if separate from a prior document).

## Steps

1. Open **Besluiten** from the navigation. The list shows every besluit across every zaak; filter by zaak number to find one case.

   ![Besluiten list view](/screenshots/tutorials/user/08-log-besluit-01.png)

2. Click **Add Item**. The dialog asks for *Zaak*, *Besluittype*, *Datum*, *Ingangsdatum*, *Vervaldatum* (optional), *Toelichting* (the motivation), and *Besluitstuk* (the linked document — see [Attach a document](./07-attach-document.md)).

   ![New besluit dialog](/screenshots/tutorials/user/08-log-besluit-02.png)

3. Fill the form and save. The besluit appears at the top of the Besluiten list and on the zaak's *Besluiten* tab.

   ![Besluiten list after adding](/screenshots/tutorials/user/08-log-besluit-03.png)

4. Set the **Resultaat** on the *Resultaten* tab (or via the zaak detail page) — *Toegekend* / *Afgewezen* / *Buiten behandeling*. Resultaat is a separate ZGW object that pairs with the besluit; both are needed before the zaak can be set to *Afgerond*.

   ![Resultaten list](/screenshots/tutorials/user/08-log-besluit-04.png)

5. Confirm the besluit is searchable via **Zoeken** — every besluit is indexed by its title, motivation and parent zaak's omschrijving, so it can be found from a global search.

   ![Search results](/screenshots/tutorials/user/08-log-besluit-05.png)

## Verification

The besluit shows up on the zaak's *Besluiten* tab, the linked besluitstuk document is reachable from the same row, the resultaat is set, the zaak's status moves to *Afgerond*, and the dashboard *Open zaken* widget no longer counts the case.

## Common issues

| Symptom | Fix |
|---|---|
| The **Besluittype** dropdown is empty | The zaaktype has no besluittypen configured — admin adds one on the zaaktype's *besluittypen* relation. |
| **Resultaat** stays empty after closing the besluit | Resultaat is a separate object, not a field on besluit — set it explicitly on the Resultaten tab. |
| Besluit appears but resultaat does not flip to *Toegekend* | They are intentionally decoupled. Some processes record the resultaat hours or days after the besluit (waiting for the formal sign-off). |

## Reference

- [Track status and result](./04-track-status.md) — companion read on the status / resultaat split.
- [Features → Decisions and Results](../../features/README.md#decisions-and-results) — feature overview.
