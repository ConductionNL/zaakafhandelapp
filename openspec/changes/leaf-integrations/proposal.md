---
kind: code
---

# Proposal: leaf-integrations

## Why

OpenRegister ships app-agnostic integration leaves (email, calendar, contacts,
files, talk, forms, and more) that any consuming app can light up per schema.
A leaf is activated by two cooperating declarations:

1. **Schema side** — the OpenRegister `Schema` entity's `configuration` block
   (`openregister/lib/Db/Schema.php`): `linkedTypes` (validated at
   `Schema.php:2198,2494-2502`, read back at `Schema.php:2631`) declares which
   integration types a schema's objects may link to, and `mailObjectTemplate`
   (`Schema.php:2204,2519-2547`) maps mail-message fields onto object
   properties so the Mail sidebar can offer a "create object from this email"
   button. These are consumed by OpenRegister's `IntegrationRegistry`
   (`openregister/lib/Service/Integration/IntegrationRegistry.php`),
   `LinkedEntityService`, `EmailService`, and — for contacts —
   `ContactMatchingService` / `ContactsController`.
2. **App side** — a detail-page widget in the app's manifest of shape
   `{"type": "integration", "integrationId": "<leaf>"}` rendering that leaf on
   the object's detail page.

Zaakafhandelapp today uses exactly **one** leaf type: `files`, five times, in
`src/manifest.json` — `zaak-files` (line 412), `taak-files` (line 559),
`bericht-files` (line 785), `besluit-files` (line 939), and `doc-files`
(line 986). Every archetype `_note` in the same manifest states the
consequence explicitly, e.g. the zaak note (line 398): *"The zaak schema
declares NO email/calendar/talk linkedTypes → no comms leaves"* — repeated for
taak (555), klant (605), medewerker (666), contactmoment (727), bericht (782),
and rol (829). For a case-handling app whose daily work is emailing citizens,
meeting deadlines, knowing who the citizen is, and receiving applications,
that is four missing integrations:

- **Mail** — a contactmoment IS frequently an email, yet an inbound email
  cannot become a contactmoment, and a zaak cannot show its email thread.
- **Calendar** — `uiterlijkeEinddatumAfdoening` / `einddatum` on zaak and
  `deadline` on taak (both surfaced as manifest columns and sort fields) never
  reach the handler's Nextcloud Calendar.
- **Contacts** — klant carries `emailadres`/`telefoonnummer` as plain fields
  (klant `_note`, manifest line 605) while the app already has a bespoke
  Contacts bridge (`lib/Controller/KlantContactsController.php`,
  `lib/Service/KlantContactSyncService.php`, `lib/Service/KlantVCardMapper.php`,
  routes `klantContacts#contactsStatus/searchContacts/importContact/
  exportContact` at `appinfo/routes.php:57-60`) that renders nowhere on the
  klant detail page.
- **Forms** — there is no intake path at all: a citizen application (aanvraag)
  submitted through Nextcloud Forms cannot start a zaak or a contactmoment.

### The no-own-register constraint (mechanism, as found in the code)

Zaakafhandelapp **does not own its schemas**. `appinfo/info.xml` lines 62-73
document it: *"src/manifest.json declares `dependencies: ["openregister"]` and
every index/detail page reads and writes through
`/apps/openregister/api/objects`"*. Sibling apps that own a register ship its
definition — including `linkedTypes` — as
`lib/Settings/{app}_register.json` (larpingapp, softwarecatalog, pipelinq,
procest, scholiq all do). Zaakafhandelapp ships **no** register JSON
(`lib/Settings/` contains only `ZaakAfhandelAppAdmin.php`); its register and
schema slugs are wired by an admin through the `{type}_source/_schema/
_register` appconfig keys served by `lib/Controller/SettingsController.php`
(`settings#index`/`settings#create`, `appinfo/routes.php:95-96`).

Therefore the schema-side half of every leaf in this change (`linkedTypes`,
`mailObjectTemplate`) MUST be **contributed to the owning register's schema
definitions on the OpenRegister side** — the register the admin-wired
`{type}_register` keys point at — and MUST NOT be shipped as schema JSON
inside zaakafhandelapp. Only the app-side half (the manifest integration
widgets and the corrected `_note` texts) lives in this repository.

## What Changes

1. **Mail**: contribute `linkedTypes: ["email"]` to the `zaak` and
   `contactmoment` schema configurations, and a
   `configuration.mailObjectTemplate` on `contactmoment` mapping mail fields
   onto the fields the manifest already renders (`notitie` ← body, `kanaal` ←
   the literal `"email"`, `startDate` ← received date, `titel` ← subject), so
   the Mail sidebar offers "create contactmoment from this email". Add an
   email leaf widget to the zaak detail page in `src/manifest.json`.
2. **Calendar**: contribute `linkedTypes: ["calendar"]` to `zaak` and `taak`;
   add calendar leaf widgets to the zaak and taak detail pages so deadlines
   (`uiterlijkeEinddatumAfdoening`, `einddatum`, taak `deadline`) and case
   appointments live in Nextcloud Calendar.
3. **Contacts**: contribute `linkedTypes: ["contacts"]` to `klant`; add a
   contacts leaf widget to the klant detail page, rendering the link the
   existing `KlantContactSyncService` import/export bridge already maintains
   (single link surface — the leaf does not duplicate the sync, it displays
   it).
4. **Forms**: contribute `linkedTypes: ["forms"]` to `zaak`; add a forms leaf
   widget to the zaak detail page so an intake form (aanvraag) can be linked
   to — and start — a case.
5. **Manifest honesty**: update every archetype `_note` whose "declares NO
   email/calendar/talk linkedTypes" claim this change makes untrue (zaak line
   398, taak 555, klant 605, contactmoment 727), per the
   `beta-surface-alignment` no-unverified-claims rule applied in reverse.

## Impact

- **Modified in this repo**: `src/manifest.json` only (new integration
  widgets + layout rows + corrected `_note` texts). No PHP changes; the leaves
  are rendered by the shared nextcloud-vue detail-page renderer and served by
  OpenRegister.
- **Contributed outside this repo**: `linkedTypes` / `mailObjectTemplate`
  configuration on the `zaak`, `taak`, `klant`, `contactmoment` schemas in
  the owning register's definition on the OpenRegister side (tracked as a
  companion contribution; this app cannot ship it).
- **Risk**: an instance whose admin-wired register lacks the contributed
  configuration renders a leaf with nothing to link — the leaf widgets must
  degrade to their empty state, never error. The contacts leaf must not race
  or duplicate `KlantContactSyncService` links.
- **Delivery**: zaakafhandelapp is on the racing-PR list — deliver via
  Codeberg PR against development only, never direct push.

## Dependencies

OpenRegister integration-leaf engine (`IntegrationRegistry`,
`LinkedEntityService`, `EmailService`, `ContactMatchingService`) and the
schema `configuration` validation in `openregister/lib/Db/Schema.php` — all
already shipped; no OpenRegister code change is required, only register
configuration.
