---
sidebar_position: 4
title: Track status and result
description: Move a zaak through its status workflow, record interim steps, and set a result when the case closes.
---

# Track status and result

Every zaak has a defined status workflow — *Intake* → *In behandeling* → *Wacht op informatie* → *Afgerond* (or *Ingetrokken*). The allowed transitions come from the zaaktype, so case handlers cannot skip required steps. This walkthrough advances a zaak through its workflow and records its result.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end the zaak's *Statussen* tab shows the chronology of every status change with timestamps and who made each transition; when the case is finished the *Resultaat* is recorded.

## Prerequisites

- A zaak with rollen assigned — see [Assign roles to a zaak](./03-manage-rollen.md).
- The zaaktype defines at least *Intake*, *In behandeling*, and *Afgerond* statuses (the standard ZGW set).

## Steps

1. Open **Statussen** from the navigation. This is the cross-zaak history — every status change on every zaak in chronological order. Use it to audit who changed what, when.

   ![Statussen list view](/screenshots/tutorials/user/04-track-status-01.png)

2. Each row shows the zaak number, the new status, the timestamp, and the user. This view is read-only — actual transitions happen on the zaak detail page, captured here automatically.

   ![Statussen detail](/screenshots/tutorials/user/04-track-status-02.png)

3. Open **Zaken**, pick the case to advance, and click into the detail page. The *Status* dropdown above the tabs offers only the transitions the zaaktype allows from the current status.

   ![Zaken list - pick a case](/screenshots/tutorials/user/04-track-status-03.png)

4. Pick the next status (e.g. *In behandeling*) and confirm. The dropdown narrows again — at *Afgerond* the only options are *Afgerond* or back to *In behandeling*. Each transition lands as a row in Statussen.

   ![Zaken detail with status workflow](/screenshots/tutorials/user/04-track-status-04.png)

5. When the case finishes, set the **Resultaat** on the *Resultaten* tab. ZGW separates *status* (workflow state) from *resultaat* (outcome — "toegekend", "afgewezen", "ingetrokken"); both need to be set before the case is fully closed.

   ![Resultaten list](/screenshots/tutorials/user/04-track-status-05.png)

## Verification

The zaak's *Statussen* tab lists every transition with timestamp and user, the *Resultaat* tab carries an outcome record, and the dashboard *Open zaken* widget no longer counts the case.

## Common issues

| Symptom | Fix |
|---|---|
| The status dropdown is empty | The zaaktype has no allowed transitions from the current status — check **Zaaktypen** → the relevant zaaktype's *statussen* configuration. |
| Resultaat dropdown lacks the outcome you need | Resultaten are also zaaktype-scoped; admin adds the missing outcome to the zaaktype. |
| A status transition appears twice | Bug — file a [GitHub issue](https://github.com/ConductionNL/zaakafhandelapp/issues) with the zaak ID and timestamps. |

## Reference

- [Add a taak to a zaak](./05-add-taak.md) — work that has to happen between statuses.
- [Log a besluit](./08-log-besluit.md) — formal decisions are separate from results.
- [Features → Case Lifecycle](../../features/README.md#case-management) — the full ZGW status set.
