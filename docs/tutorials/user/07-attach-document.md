---
sidebar_position: 7
title: Attach a document to a zaak
description: Add a file as a zaakinformatieobject — the ZGW link between a document and a zaak.
---

# Attach a document to a zaak

ZGW models attachments through *zaakinformatieobjecten* — a typed link between a *document* (the file plus metadata) and a *zaak*. Both the document and the link have their own audit trail.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end you have uploaded a file, registered it as a document with a *informatieobjecttype*, and linked it to a zaak via a zaakinformatieobject so it shows up on the zaak's *Documenten* tab.

## Prerequisites

- A zaak in *In behandeling* status.
- The file you want to attach. PDFs and Office documents are the most common; the underlying register accepts any MIME type.

## Steps

1. Open **Documenten** from the navigation. The list shows every document registered in ZAA — uploaded by anyone, attached to any case.

   ![Documenten list view](/screenshots/tutorials/user/07-attach-document-01.png)

2. Click **Add Item**. The dialog asks for *Titel*, *Beschrijving*, *Informatieobjecttype* (the document classification — *Brief*, *Besluitstuk*, *Bewijsstuk*, …), *Vertrouwelijkheid*, *Auteur*, *Taal*, and the file itself.

   ![New document dialog](/screenshots/tutorials/user/07-attach-document-02.png)

3. Pick a file, fill the metadata, save. The document appears at the top of the Documenten list. It is *not yet* attached to a zaak — that is the next step.

   ![Documenten list after adding](/screenshots/tutorials/user/07-attach-document-03.png)

4. Open **Zaakinformatieobjecten** and create a new link. Pick the document you just registered, pick the target zaak, set *aardRelatieWeergave* (e.g. *Onderwerp* or *Bijlage*). Save — the document is now linked.

   ![Zaakinformatieobjecten list](/screenshots/tutorials/user/07-attach-document-04.png)

5. Open the zaak's detail page from **Zaken**. The *Documenten* tab now lists the file with its title, informatieobjecttype, upload date and uploader. Clicking it opens the file viewer or downloads, depending on MIME type.

   ![Zaken list - confirm attachment](/screenshots/tutorials/user/07-attach-document-05.png)

## Verification

The file shows up on the zaak's *Documenten* tab with the correct informatieobjecttype, the zaakinformatieobject row is visible in the cross-cutting list, and downloading from the tab returns the uploaded bytes.

## Common issues

| Symptom | Fix |
|---|---|
| Upload fails with a 413 error | Server is rejecting the file size — admin raises Nextcloud's PHP `upload_max_filesize` and `post_max_size`, plus the OpenRegister object limit if configured. |
| **Informatieobjecttype** dropdown is empty | The schema is mapped but no types are seeded. Admin imports the ZGW informatieobjecttypen via the admin settings page. |
| Document uploads but does not show on the zaak | The zaakinformatieobject link is missing — step 4. Make a quick habit of creating both objects in one go. |

## Reference

- [Log a besluit](./08-log-besluit.md) — a besluit usually has its own besluitstuk document.
- [Features → Documents](../../features/README.md#documents) — feature overview.
