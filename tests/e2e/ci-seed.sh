#!/usr/bin/env bash
#
# SPDX-FileCopyrightText: 2026 Conduction B.V.
# SPDX-License-Identifier: EUPL-1.2
#
# Provision zaakafhandelapp's OpenRegister register + schemas on a freshly
# installed Nextcloud, for the shared `E2E Tests (Playwright)` CI job.
#
# Wired up as the workflow's `playwright-seed-command`. That step runs AFTER
# `php -S` is up and with cwd set to the Nextcloud server root, so this is
# invoked as:
#
#     playwright-seed-command: 'bash apps/zaakafhandelapp/tests/e2e/ci-seed.sh'
#
# WHY THIS IS NEEDED
# ------------------
# zaakafhandelapp is a manifest-v2 (CnAppRoot) app: every index/detail page in
# `src/manifest.json` declares `register: "zaakafhandelapp"` plus a schema slug
# (zaak / taak / klant / status / zaaktype / medewerker / bericht / besluit /
# resultaat / rol), and the shared object store reads and writes them DIRECTLY
# through `/apps/openregister/api/objects/{register}/{schema}`.
#
# Unlike its sibling apps, zaakafhandelapp ships NO `lib/Settings/*_register.json`,
# has NO `IRepairStep` that imports one, and NO import route of its own
# (`appinfo/routes.php` registers only `settings#index` / `settings#create`).
# `occ app:enable zaakafhandelapp` therefore provisions NOTHING — the app
# enables cleanly, the SPA boots, and the register simply does not exist. The
# register on the shared dev instance was created by hand; see the
# "Environment note" at the bottom of tests/e2e/workflows/BUGS.md.
#
# The suite's failure mode in that state is NOT subtle but it IS misleading:
# `tests/e2e/workflows/fixtures.ts` `init()` probes
# `GET /apps/openregister/api/registers/zaakafhandelapp` and throws, so all
# three workflow specs fail in `beforeAll` with an error that reads like a
# fixture bug.
#
# So this script imports the register + schemas EXPLICITLY over the admin HTTP
# API (which has a real session and passes OpenRegister's RBAC) with
# `force=true` to defeat the configuration version guard, and then VERIFIES the
# register and every schema slug the suite resolves by actually exist. A failed
# provision becomes ONE loud step failure here instead of a wall of misleading
# spec failures later.
#
# The payload (`ci-register.json`, next to this script) is an unmodified
# `GET /api/registers/{id}/export` of the reference register, with only
# instance-local bookkeeping stripped (folder id, usage counters, quota,
# created/updated/deleted, owner/application/authorization). It is the exact
# shape the specs were built and verified against.
#
# It is idempotent: OpenRegister's importer matches registers and schemas by
# slug and updates in place, and re-running only re-verifies.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/ci-register.json"

# ── Target resolution ────────────────────────────────────────────────────────
# The shared workflow's "Seed test data" step exports BASE_URL / NEXTCLOUD_URL /
# NC_BASE_URL / ADMIN_USER / ADMIN_PASSWORD, but accept the unset case too and
# fall back to the CI runner's own `php -S 0.0.0.0:8080`.
#
# That fallback is gated on actually being in CI. On a developer box
# `localhost:8080` is the SHARED dev container, and this script performs ADMIN
# WRITES — it must never silently import a register into somebody else's
# environment. Off CI, an unset target is a hard error.
BASE="${PLAYWRIGHT_BASE_URL:-${BASE_URL:-${NEXTCLOUD_URL:-${NC_BASE_URL:-}}}}"
if [ -z "$BASE" ]; then
	if [ "${GITHUB_ACTIONS:-}" = "true" ] || [ "${CI:-}" = "true" ]; then
		BASE="http://localhost:8080"
	else
		echo "ERROR: no base URL set. Export PLAYWRIGHT_BASE_URL or BASE_URL." >&2
		echo "       Refusing to default to http://localhost:8080 outside CI —" >&2
		echo "       that is the SHARED dev container and this script writes to it." >&2
		exit 1
	fi
fi
BASE="${BASE%/}"

USER_NAME="${ADMIN_USER:-${NC_ADMIN_USER:-admin}}"
USER_PASS="${ADMIN_PASSWORD:-${NC_ADMIN_PASS:-admin}}"

echo "[ci-seed] target: ${BASE}"

if [ ! -f "$CONFIG_FILE" ]; then
	echo "::error::Missing ${CONFIG_FILE} — the register/schema payload this script imports."
	exit 1
fi

# ── 1. Import the register + schemas ─────────────────────────────────────────
# `configurations#import` is OpenRegister's generic importer. It is admin-only
# (`isCurrentUserAdmin()` → 403 otherwise) and reads the payload from an
# uploaded file under key `file`. `force` is compared
# `=== 'true' || === true`, so a multipart form field spelled "true" is
# accepted — this is the one place where the string form is correct.
IMPORT_URL="${BASE}/index.php/apps/openregister/api/configurations/import"
echo "[ci-seed] POST ${IMPORT_URL} (force=true, appId=zaakafhandelapp)"

IMPORT_BODY="$(mktemp)"
IMPORT_CODE="$(
	curl -sS -o "$IMPORT_BODY" -w '%{http_code}' \
		-u "${USER_NAME}:${USER_PASS}" \
		-X POST \
		-H 'OCS-APIRequest: true' \
		-F "file=@${CONFIG_FILE};type=application/json" \
		-F 'force=true' \
		-F 'appId=zaakafhandelapp' \
		"$IMPORT_URL" || echo 000
)"

echo "[ci-seed] import HTTP ${IMPORT_CODE}"
head -c 2000 "$IMPORT_BODY"; echo

if [ "$IMPORT_CODE" != "200" ]; then
	echo "::error::OpenRegister configuration import failed (HTTP ${IMPORT_CODE}). Every zaakafhandelapp data page reads through this register; the workflow specs cannot even reach beforeAll without it."
	exit 1
fi

# ── 2. Verify the register and schemas are actually there ────────────────────
# "Import successful" is NOT the same as the register existing: the importer
# returns that message with EMPTY `registers`/`schemas` arrays when the version
# guard short-circuits it. Verify against OpenRegister directly, using the same
# slugs the app resolves by (src/manifest.json page configs and
# tests/e2e/workflows/fixtures.ts).
verify() {
	python3 - "$1" "$2" <<'PY'
import json, sys
path, kind = sys.argv[1], sys.argv[2]
required = {
    'registers': ['zaakafhandelapp'],
    'schemas': ['zaak', 'taak', 'klant', 'status', 'zaaktype',
                'medewerker', 'bericht', 'besluit', 'resultaat', 'rol'],
}[kind]
with open(path) as fh:
    raw = fh.read()
try:
    body = json.loads(raw)
except json.JSONDecodeError:
    print(f'::error::{kind} endpoint did not return JSON. First 500 bytes:')
    print(raw[:500])
    sys.exit(1)
items = body if isinstance(body, list) else body.get('results', [])
slugs = {i.get('slug') for i in items if isinstance(i, dict)}
missing = [s for s in required if s not in slugs]
print(f'[ci-seed] {kind} present: {sorted(s for s in slugs if s)}')
if missing:
    print(f'::error::zaakafhandelapp {kind} missing after import: {missing}')
    sys.exit(1)
print(f'[ci-seed] {kind} OK ({len(required)} required slugs present)')
PY
}

REG_BODY="$(mktemp)"
curl -sS -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/registers?_limit=300" -o "$REG_BODY"
verify "$REG_BODY" registers

SCH_BODY="$(mktemp)"
curl -sS -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/schemas?_limit=1000" -o "$SCH_BODY"
verify "$SCH_BODY" schemas

# The workflow fixtures resolve the register by slug through the SHOW route, not
# the list — a slug that lists fine but does not resolve through
# `registers/{slug}` would still throw in `fixtures.ts` `init()`. Probe the
# exact URL that module probes.
FIX_CODE="$(curl -sS -o /dev/null -w '%{http_code}' -u "${USER_NAME}:${USER_PASS}" \
	-H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/registers/zaakafhandelapp" || echo 000)"
echo "[ci-seed] fixtures.ts probe GET registers/zaakafhandelapp -> ${FIX_CODE}"
if [ "$FIX_CODE" != "200" ]; then
	echo "::error::GET /apps/openregister/api/registers/zaakafhandelapp returned ${FIX_CODE}. tests/e2e/workflows/fixtures.ts init() throws on this exact call, failing all three workflow specs in beforeAll."
	exit 1
fi

# And the object collection route the manifest UI and the fixtures actually
# read/write through. A register+schema that exist but whose object table was
# not created would pass every check above and still fail every data spec.
OBJ_CODE="$(curl -sS -o /dev/null -w '%{http_code}' -u "${USER_NAME}:${USER_PASS}" \
	-H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/objects/zaakafhandelapp/zaak?_limit=1" || echo 000)"
echo "[ci-seed] object collection GET objects/zaakafhandelapp/zaak -> ${OBJ_CODE}"
if [ "$OBJ_CODE" != "200" ]; then
	echo "::error::GET /apps/openregister/api/objects/zaakafhandelapp/zaak returned ${OBJ_CODE}. The register exists but its objects are not readable, so every index page and every fixture list would fail."
	exit 1
fi

echo "[ci-seed] zaakafhandelapp register + schemas provisioned."

# ── 3. Warm the SPA so the first spec doesn't pay the cold start ─────────────
# The shared workflow serves Nextcloud with `php -S 0.0.0.0:8080`. The first
# hit pays a cold opcache and the first parse of the webpack bundle, and the
# effect lands entirely on whichever spec happens to run first — it measures
# server warm-up rather than anything about its own assertion.
#
# So warm it here, in the environment-preparation step where it belongs. The
# alternative — raising that spec's timeout — would hide the cold start inside
# the assertion instead of removing it, and would keep drifting upward.
# Failures are ignored on purpose: this is a warm-up, not a gate. The real
# checks are above and below.
for path in \
	"/index.php/apps/zaakafhandelapp/" \
	"/index.php/apps/zaakafhandelapp/zaken" \
	"/index.php/apps/openregister/api/registers?_limit=1"
do
	code="$(curl -sS -o /dev/null -w '%{http_code}' -u "${USER_NAME}:${USER_PASS}" \
		-H 'OCS-APIRequest: true' "${BASE}${path}" || echo 000)"
	echo "[ci-seed] warm ${path} -> ${code}"
done

# Pull the main webpack bundle once so it is in the page cache.
#
# Do NOT hardcode the URL. Nextcloud serves an app's assets from whichever apps
# directory it was installed into — `/apps/<app>/js/...` on the CI runner,
# `/custom_apps/<app>/js/...` in the docker dev images — and asking for the
# wrong one does not 404. It returns **HTTP 200 with `text/html`**: the NC error
# page, served through index.php. A status-code check therefore reports success
# while fetching an HTML page instead of a multi-MB bundle, so the warm-up
# silently warms nothing.
#
# Read the real src out of the rendered app page instead, and verify the
# response is actually JavaScript.
APP_HTML="$(mktemp)"
curl -sS -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/zaakafhandelapp/" -o "$APP_HTML" || true

# `|| true` is load-bearing: grep exits 1 when it matches nothing, and under
# `set -euo pipefail` that aborts the script right here — so the case the gate
# below exists to explain (no bundle) would die with a bare non-zero exit and
# none of the diagnosis. Let it fall through to the gate instead.
BUNDLE_SRC="$(grep -oE 'src="[^"]*zaakafhandelapp-main[^"]*"' "$APP_HTML" \
	| head -1 | sed 's/^src="//; s/"$//' || true)"

if [ -n "$BUNDLE_SRC" ]; then
	BUNDLE_INFO="$(curl -sS -o /dev/null \
		-w '%{http_code} %{content_type} %{size_download}' \
		-u "${USER_NAME}:${USER_PASS}" "${BASE}${BUNDLE_SRC}" || echo '000 - 0')"
	echo "[ci-seed] warm bundle ${BUNDLE_SRC} -> ${BUNDLE_INFO}"
else
	echo "[ci-seed] could not locate the bundle src in the rendered app page."
	BUNDLE_INFO=""
fi

# On CI this is a GATE, not a warm-up.
#
# The single most likely way this job "succeeds" dishonestly is by passing
# without ever loading the app — and the environment hides it well: when the
# bundle is absent, Nextcloud does not 404. It serves its HTML error page with
# **HTTP 200 and Content-Type text/html**, so `npm run build` producing nothing
# looks, to every status-code check in the pipeline, exactly like success.
#
# The specs are the honest signal; this check just makes the cause loud and
# immediate instead of arriving as a wall of selector timeouts.
if [ "${GITHUB_ACTIONS:-}" = "true" ] || [ "${CI:-}" = "true" ]; then
	case "$BUNDLE_INFO" in
		*javascript*)
			echo "[ci-seed] bundle verified as JavaScript."
			;;
		*)
			echo "::error::The zaakafhandelapp frontend bundle did not serve as JavaScript (got: ${BUNDLE_INFO:-<not found>})."
			echo "::error::The SPA cannot mount, so every UI spec would fail on a selector timeout with a misleading cause."
			echo "::error::Check the 'Build app frontend' step — a missing bundle returns HTTP 200 text/html, not 404."
			exit 1
			;;
	esac
fi

echo "[ci-seed] done."
