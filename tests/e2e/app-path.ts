/*
 * SPDX-FileCopyrightText: 2026 Conduction B.V.
 * SPDX-License-Identifier: EUPL-1.2
 *
 * The ONE place the e2e suite spells the app's URL prefix.
 *
 * WHY THE `/index.php` PREFIX IS MANDATORY
 * ----------------------------------------
 * Every spec used to hardcode `/apps/zaakafhandelapp`. That "pretty" form only
 * resolves behind a web server that applies Nextcloud's `.htaccess` rewrite —
 * i.e. the Apache in the docker dev container, which is the only place the
 * suite had ever run.
 *
 * The shared `E2E Tests (Playwright)` CI job serves Nextcloud with
 * `php -S 0.0.0.0:8080` from the server root. The PHP built-in server applies
 * no rewrite rules at all, so `/apps/zaakafhandelapp/` is just a path that does
 * not exist on disk and it answers with its own 404 page:
 *
 *     Not Found — The requested resource /apps/zaakafhandelapp/ was not found
 *     on this server.
 *
 * The SPA never mounts, and every single spec then fails on a selector timeout
 * for `cn-app-root` / `cn-nav-entry-*` — a failure that reads like a broken
 * frontend rather than a URL that never reached Nextcloud. (Observed: 91 of 94
 * tests failed this way on the first CI run.)
 *
 * `/index.php/apps/zaakafhandelapp` is Nextcloud's front-controller form. It is
 * what `php -S` needs AND what Apache serves identically, so it is correct in
 * both environments — there is no environment switch to get wrong. The visual
 * project in this same suite already used this form
 * (tests/e2e/visual/zaakafhandelapp.visual.spec.ts); the rest of the suite did
 * not, which is exactly why the gap went unnoticed.
 */

/** The app's URL prefix, front-controller form. Works with and without rewrite. */
export const APP = '/index.php/apps/zaakafhandelapp'
