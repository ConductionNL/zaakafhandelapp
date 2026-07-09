---
sidebar_position: 3
title: Manage Zaak Afhandel App settings
description: Open the ZAA settings, run the OpenRegister configuration import, and tune the app-wide options.
---

# Manage Zaak Afhandel App settings

ZAA's settings cover two surfaces: an in-app **Settings** page (under the navigation divider) for the day-to-day configuration, and a Nextcloud admin section (**Settings → Administration → Zaak Afhandel App**) for the cross-cutting integration options. This walkthrough opens both, runs the OpenRegister configuration import, and confirms the app is wired correctly.

:::warning Deprecated

ZAA is no longer actively developed. See the [deprecation notice](../../intro.md).

:::

## Goal

By the end the OpenRegister configuration import has run cleanly, the in-app settings page reports the correct register/schema mappings, and the Nextcloud admin section is reachable.

## Prerequisites

- Admin on the Nextcloud instance.
- The **OpenRegister** app installed and enabled.
- The Conduction *zaakafhandelapp* register and its schemas available (bundled with the app — the configuration import handles it).

## Steps

1. Open **Settings** from the in-app navigation. The page lists every register/schema mapping ZAA needs — *zaak*, *zaaktype*, *rol*, *taak*, *klant*, *medewerker*, *contactmoment*, *bericht*, *besluit*, *document*, *resultaat*, *statustype*, *zaakinformatieobject*.

   ![In-app settings page](/screenshots/tutorials/admin/03-admin-settings-01.png)

2. Each row shows the target *Register* and *Schema*. On a broken install some rows will be empty; on a healthy install every row is populated.

   ![Settings - mappings (top)](/screenshots/tutorials/admin/03-admin-settings-02.png)

3. To (re)run the configuration import, scroll to the *Configuration* section. Click **Re-import configuration** — the action recreates the register, all schemas and the mappings. Existing zaken/taken/etc. data is left alone (matched by `uuid`).

   ![Settings - configuration import](/screenshots/tutorials/admin/03-admin-settings-03.png)

4. After the import, every row should be populated and the page should report a "Configuration up to date" banner. If any row stays empty, check the Nextcloud log for the import error.

   ![Settings - mappings (bottom)](/screenshots/tutorials/admin/03-admin-settings-04.png)

5. Open the Nextcloud admin section at **Settings → Administration → Zaak Afhandel App**. This carries the cross-cutting toggles — the ZGW API endpoint Zaak Afhandel App proxies to, audit-trail retention, and the gateway / service-bus options.

   ![Nextcloud admin section](/screenshots/tutorials/admin/03-admin-settings-05.png)

## Verification

The in-app settings page reports every schema mapping populated. The Nextcloud admin section is reachable. Creating a test zaak (see [Register a new zaak](../user/02-create-zaak.md)) succeeds without an "OpenRegister error" banner.

## Common issues

| Symptom | Fix |
|---|---|
| Settings page reports rows unmapped after re-import | The import is failing server-side — check `nextcloud.log` for the *zaakafhandelapp* configuration error. Often a stale OpenRegister version pair mismatches the import API; bring both apps to a compatible release. |
| Re-import button does nothing visible | The button posts asynchronously; watch the in-app *Activity* widget or `nextcloud.log` for the import progress and any failure. |
| Nextcloud admin section is missing | The `<settings>` block in `appinfo/info.xml` did not register on app install. Disable and re-enable the app: `occ app:disable zaakafhandelapp && occ app:enable zaakafhandelapp`. |
| The gateway/service-bus endpoint stays disconnected | The endpoint URL is empty or wrong; the admin section's *Gateway* row shows the current value. Once correct, the proxied ZGW endpoints (`/api/zrc/...`) start responding. |

## Reference

- [Open Zaak Afhandel App for the first time](../user/01-first-launch.md) — the user-facing check that the import worked.
- [Configure zaaktypen](./01-configure-zaaktypen.md) — first thing to set up after the register import succeeds.
- [Manage medewerkers](./02-manage-medewerkers.md) — second thing.
