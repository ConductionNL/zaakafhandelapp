---
sidebar_position: 6
title: Record a contactmoment
description: Log every interaction with a citizen — phone call, email, counter visit — against the relevant zaak.
---

# Record a contactmoment

Every interaction with a citizen is recorded as a *contactmoment* — phone calls, e-mails, counter visits, replies on a written notice. The audit trail this builds is critical for ZGW compliance and for the next handler picking up the case.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end the contactmoment is logged with the channel (phone / email / counter / letter), the klant, the linked zaak, a free-text note, and shows up under both the klant's history and the zaak's *Contactmomenten* tab.

## Prerequisites

- A klant record for the citizen. If they are new, create one from **Klanten** first.
- A zaak the contact relates to. Citizen-led queries can also create the zaak as part of the same interaction.

## Steps

1. Open **Contactmomenten** from the navigation. The list view is sorted newest-first by default — convenient for "what just happened on this case".

   ![Contactmomenten list view](/screenshots/tutorials/user/06-record-contactmoment-01.png)

2. Click **Add Item**. The dialog asks for *Klant*, *Zaak*, *Kanaal* (phone / email / counter / letter), *Onderwerp*, *Toelichting*, and *Medewerker* (defaults to you).

   ![New contactmoment dialog](/screenshots/tutorials/user/06-record-contactmoment-02.png)

3. Save. The contactmoment appears at the top of the list and counts on the dashboard *Contactmomenten* widget.

   ![Contactmomenten list after adding](/screenshots/tutorials/user/06-record-contactmoment-03.png)

4. Open **Berichten** for written interactions you want to preserve verbatim (replies on a notice, scanned letters, email transcripts). Berichten are the body of the message; contactmomenten are the metadata pointer.

   ![Berichten list](/screenshots/tutorials/user/06-record-contactmoment-04.png)

5. Open the klant's detail page from **Klanten**. The *Contactmomenten* tab shows every interaction across every case — useful when a returning citizen calls and you need their history.

   ![Klant detail with contact history](/screenshots/tutorials/user/06-record-contactmoment-05.png)

## Verification

The new contactmoment appears at the top of the Contactmomenten list, on the linked zaak's *Contactmomenten* tab, and on the klant's *Contactmomenten* tab. The dashboard *Contactmomenten* widget total increments.

## Common issues

| Symptom | Fix |
|---|---|
| The **Klant** dropdown does not contain the caller | The klant record does not exist yet — create one from **Klanten** and come back. ZAA does not yet auto-suggest from phone-number lookups. |
| Kanaal is missing the channel you used | Channel list is configured per deployment — admin extends the `kanaal` enum on the contactmoment schema in OpenRegister. |
| The bericht body did not save | Berichten are a separate object — they need to be saved on the **Berichten** dialog, not in the contactmoment toelichting. |

## Reference

- [Attach a document to a zaak](./07-attach-document.md) — formal letters belong on the zaak as documents, not in contactmoment text.
- [Features → Customer Interactions](../../features/README.md#contact-moments) — the contactmoment feature page.
