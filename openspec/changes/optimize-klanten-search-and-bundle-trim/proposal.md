---
kind: code
---

# Proposal: optimize-klanten-search-and-bundle-trim

## Why

**1. Every keystroke of the klant/contact search full-scans the entire
klanten register.**

`KlantContactSyncService::searchContacts()`
(`lib/Service/KlantContactSyncService.php:122-150`) calls
`getLinkedContactsUids()` on every invocation
(`lib/Service/KlantContactSyncService.php:134`), which does:

```php
private function getLinkedContactsUids(): array
{
    $klanten = $this->objectService->getAllObjects(self::KLANT_TYPE);
    ...
}
```

(`lib/Service/KlantContactSyncService.php:412-426`). `getAllObjects()`
(`lib/Service/ObjectQueryService.php:94-97`, wrapped by
`lib/Service/ObjectService.php:105-108`) is declared
`getAllObjects(string $objectType, ?int $limit=null, ?int $offset=null)`
and is called here with **no limit/offset** — an unbounded
`$mapper->findAll($limit, $offset)` that fetches, hydrates and casts
**every** `klant` object in the register, just to build a set of
`contactsUid` values so the addressbook search results can be flagged
`alreadyLinked`.

This method sits directly behind a public, frequently-invoked HTTP
endpoint: `GET /api/klanten/contacts/search`
(`appinfo/routes.php:51`, `klanten#searchContacts`), which
`src/modals/klanten/SearchKlantModal.vue` and
`src/modals/medewerkers/SearchKlantModal.vue` call as the user types
(debounced, but still one full klanten-register scan per debounce
tick). A municipality with a few thousand klanten will pay a full
register read on every search-as-you-type keystroke tick, for a result
that only needs a `Set<string>` of `contactsUid` values.

This is the exact "query cost: unbounded search feeding a
list/count" pattern flagged for lens 1. `openspec/specs/klanten-addressbook-sync/spec.md`
REQ-001 already requires search results to be bounded, but has no
constraint on how the `alreadyLinked` computation itself is sourced —
which is precisely the gap this change closes.

**2. Wholesale `lodash` import for two functions.**

Ten files do `import _ from 'lodash'`
(e.g. `src/views/medewerkers/MedewerkerList.vue:75`,
`src/modals/zaken/AddRolToZaak.vue:47`,
`src/sidebars/search/SearchSideBar.vue:228`) but between them only call
two lodash functions: `_.upperFirst` (7 call-sites, e.g.
`src/views/medewerkers/MedewerkerList.vue:40`,
`src/sidebars/search/SearchSideBar.vue:26,121,176`,
`src/views/klanten/KlantenList.vue:46`,
`src/modals/medewerkers/SearchKlantModal.vue:106`,
`src/modals/klanten/SearchKlantModal.vue:116`) and `_.cloneDeep` (5
call-sites, e.g. `src/modals/zaken/AddRolToZaak.vue:121`,
`src/modals/zaken/AddBerichtToZaak.vue:117`,
`src/modals/zaken/AddTaakToZaak.vue:125`,
`src/modals/contactMomenten/ContactMomentenForm.vue:960`,
`src/modals/contactMomenten/ViewContactMoment.vue:503`). Webpack's
`splitChunks` (`webpack.config.js`) is otherwise well-tuned (shared
nc-vue/vendor chunks, `runtimeChunk`), but `import _ from 'lodash'`
defeats tree-shaking regardless — the full CJS `lodash` package is
pulled into whichever chunk each of these ten files lands in.

**3. Dead `@fortawesome` dependency.**

`package.json` (`package.json:35-36`) declares
`@fortawesome/fontawesome-svg-core` and
`@fortawesome/free-solid-svg-icons` as direct dependencies, but a
repo-wide search finds **zero** imports of `fortawesome` anywhere
under `src/`. Per the fleet convention (icons come from
`vue-material-design-icons`, already a dependency and used throughout
the app), this is dead weight shipped in `package-lock.json` /
`node_modules` for no runtime benefit — and directly the "fortawesome
NOT [from nc-vue]" duplication the fleet review flags when it shows up
unused.

## What Changes

- Add a bounded, purpose-built lookup for the `alreadyLinked` flag:
  either (a) a filtered `findAll` call scoped to
  `['filters' => ['contactsUid' => ['!=' => null]]]` with a hard
  result cap, or (b) delegate the "does a klant with this contactsUid
  exist" check to a per-uid existence query invoked only for the
  contacts actually returned by the addressbook search (bounded by the
  addressbook manager's own `limit: 50`,
  `lib/Service/KlantContactSyncService.php:131`) instead of
  pre-loading every klant up front. Prefer (b): it turns an O(register
  size) read into an O(search-result size) read (≤ 50 lookups, each
  a targeted filter query).
- Replace all ten `import _ from 'lodash'` statements with named,
  tree-shakeable imports: `import upperFirst from
  'lodash/upperFirst'` and `import cloneDeep from
  'lodash/cloneDeep'` (per call-site need), updating the ~12 call
  sites from `_.upperFirst(...)` / `_.cloneDeep(...)` to the bare
  imported function.
- Remove `@fortawesome/fontawesome-svg-core` and
  `@fortawesome/free-solid-svg-icons` from `package.json`
  `dependencies` (confirmed zero usages under `src/`); regenerate
  `package-lock.json`.
- Add `klanten-addressbook-sync` REQ-001 clarifying language (MODIFIED)
  that the `alreadyLinked` computation MUST NOT require reading the
  full klanten register per search request.

## Impact

- **Affected specs**: `klanten-addressbook-sync` (REQ-001 modified).
- **Affected code**: `lib/Service/KlantContactSyncService.php`
  (`getLinkedContactsUids`/`searchContacts`), ~10 `.vue`/`.js` files
  for the lodash import swap, `package.json` + `package-lock.json` for
  the fortawesome removal.
- No route, schema, or API response-shape changes — `searchContacts`'s
  JSON response (`uid`, `addressbook`, display fields, `alreadyLinked`)
  is unchanged; only how `alreadyLinked` is computed changes.
- No BREAKING changes.
