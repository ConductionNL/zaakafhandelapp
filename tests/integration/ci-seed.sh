#!/usr/bin/env bash
#
# SPDX-FileCopyrightText: 2026 Conduction B.V.
# SPDX-License-Identifier: EUPL-1.2
#
# Provision what tests/integration/zaakafhandelapp.postman_collection.json needs
# on a freshly installed Nextcloud, for the shared `Integration Tests (Newman)`
# CI job.
#
# Wired up as the workflow's `newman-seed-command`. That step runs AFTER
# `php -S` is up and with cwd set to the Nextcloud server root, so this is
# invoked as:
#
#     newman-seed-command: 'bash apps/zaakafhandelapp/tests/integration/ci-seed.sh'
#
# WHY THIS IS NEEDED — TWO SEPARATE THINGS ARE MISSING
# ----------------------------------------------------
# Measured on run 31046181191 (branch development, commit c790bb0): 45 requests,
# 55 assertions, 38 passed, 17 FAILED. Reproduced exactly on a local replica.
# The 17 are two clusters, not one:
#
# 1. THE REGISTER DOES NOT EXIST.
#    `occ app:enable zaakafhandelapp` provisions nothing — this app ships no
#    `lib/Settings/*_register.json`, no `IRepairStep`, and no import route (see
#    the long note in tests/e2e/ci-seed.sh, which is why that script exists for
#    the Playwright job). The collection's first request resolves the register
#    by slug and finds nothing, and its seed POST 404s. 3 assertions.
#
# 2. THE APP IS NOT BOUND TO THAT REGISTER.
#    Importing the register is NOT enough. `ObjectMapperService::getMapper()`
#    reads three appconfig keys per object type — `<type>_source`,
#    `<type>_register`, `<type>_schema` — and with `_source` still at its
#    'internal' default it falls through to
#    `throw new InvalidArgumentException("Unknown object type: $objectType")`,
#    which is an uncaught 500 and an HTML error page. That is where every
#    `JSONError  Unexpected token '<' at 1:1` in the CI log comes from: the body
#    was Nextcloud's error page, not JSON. 14 assertions across zaken, taken and
#    klanten.
#
#    The collection was authored against the shared dev instance, where those
#    keys had been set BY HAND. It even documents the state it expects: the
#    quarantined zaaktypen test says "Wiring a zaaktypen_source/register/schema
#    (like zaken/taken/klanten) would make this 200". So zaken/taken/klanten
#    configured, and nothing else, is exactly the environment it describes —
#    which is why this script binds those three and deliberately leaves
#    statussen / resultaten / rollen / besluiten / zaaktypen / documenten
#    unconfigured. Binding more would flip the quarantine assertions (which
#    assert the CURRENT 500) from green to red.
#
# WHY THE REGISTER IS PASSED AS A NUMERIC ID, NOT THE SLUG
# --------------------------------------------------------
# It is tempting to write `zaken_register=zaakafhandelapp` — the slug is stable
# across re-imports and the numeric id is not. Measured: that half-works, which
# is the dangerous kind of wrong. `ObjectMapperService` hands the value straight
# to OpenRegister's `ObjectService::getMapper(register:, schema:)`. On the READ
# path a slug resolves and `GET /api/klanten` returns 200. On the WRITE path it
# does not: the schema comes back null and OpenRegister dies with
#
#   TypeError: CascadingHandler::handlePreValidationCascading():
#              Argument #2 ($schema) must be of type ...\Db\Schema, null given
#
# i.e. a 500 on create while the list endpoint looks perfectly healthy. So
# resolve the slug to its id HERE, once, and store the id. (The collection's own
# "POST /settings" test does the same thing — it writes `{{zaakRegister}}`,
# the numeric id its preflight resolved.)
#
# It is idempotent: the import matches by slug and updates in place, and the
# settings POST is a plain overwrite.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"

# ── Target resolution ────────────────────────────────────────────────────────
# Mirrors tests/e2e/ci-seed.sh, including its safety rule: off CI an unset
# target is a hard error, because `localhost:8080` on a developer box is the
# SHARED dev container and this script performs ADMIN WRITES to appconfig.
BASE="${BASE_URL:-${NEXTCLOUD_URL:-${NC_BASE_URL:-}}}"
if [ -z "$BASE" ]; then
	if [ "${GITHUB_ACTIONS:-}" = "true" ] || [ "${CI:-}" = "true" ]; then
		BASE="http://localhost:8080"
	else
		echo "ERROR: no base URL set. Export BASE_URL." >&2
		echo "       Refusing to default to http://localhost:8080 outside CI —" >&2
		echo "       that is the SHARED dev container and this script writes to it." >&2
		exit 1
	fi
fi
BASE="${BASE%/}"

USER_NAME="${ADMIN_USER:-${NC_ADMIN_USER:-admin}}"
USER_PASS="${ADMIN_PASSWORD:-${NC_ADMIN_PASS:-admin}}"

echo "[newman-seed] target: ${BASE}"

# ── 1. Import the register + schemas ─────────────────────────────────────────
# Delegated, not duplicated: tests/e2e/ci-seed.sh already owns the payload
# (ci-register.json), the importer call and the post-import verification, and a
# second copy would drift. ZAA_SEED_SKIP_FRONTEND stops it before its SPA
# warm-up and bundle gate — the Newman job has no frontend build step, so that
# gate would fail on the absence of a bundle Newman never loads.
echo "[newman-seed] importing register via tests/e2e/ci-seed.sh"
BASE_URL="$BASE" ADMIN_USER="$USER_NAME" ADMIN_PASSWORD="$USER_PASS" \
	ZAA_SEED_SKIP_FRONTEND=1 \
	bash "${REPO_ROOT}/tests/e2e/ci-seed.sh"

# ── 2. Resolve the register slug to its numeric id ───────────────────────────
# See the header: the write path needs the id, not the slug.
REG_BODY="$(mktemp)"
REG_CODE="$(curl -sS -o "$REG_BODY" -w '%{http_code}' \
	-u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/registers?_limit=300" || echo 000)"
echo "[newman-seed] registers list -> HTTP ${REG_CODE}"
if [ "$REG_CODE" != "200" ]; then
	echo "::error::GET /apps/openregister/api/registers returned ${REG_CODE}. First 300 bytes:"
	head -c 300 "$REG_BODY"; echo
	exit 1
fi

REGISTER_ID="$(python3 - "$REG_BODY" <<'PY'
import json, sys
with open(sys.argv[1]) as fh:
    raw = fh.read()
try:
    body = json.loads(raw)
except json.JSONDecodeError:
    print('::error::registers endpoint did not return JSON. First 500 bytes:', file=sys.stderr)
    print(raw[:500], file=sys.stderr)
    sys.exit(1)
items = body if isinstance(body, list) else body.get('results', [])
for item in items:
    if isinstance(item, dict) and item.get('slug') == 'zaakafhandelapp':
        print(item['id'])
        sys.exit(0)
print("::error::no register with slug 'zaakafhandelapp' after import.", file=sys.stderr)
sys.exit(1)
PY
)"
echo "[newman-seed] resolved register slug=zaakafhandelapp -> id=${REGISTER_ID}"

# ── 3. Bind the app's object types to that register ──────────────────────────
# Only the three the collection exercises through the app's own API. See the
# header for why binding more would turn the quarantine assertions red.
SET_BODY="$(mktemp)"
SET_CODE="$(curl -sS -o "$SET_BODY" -w '%{http_code}' \
	-u "${USER_NAME}:${USER_PASS}" \
	-X POST \
	-H 'OCS-APIRequest: true' \
	-H 'Content-Type: application/json' \
	-d "{
	      \"zaken_source\":\"openregister\",   \"zaken_register\":\"${REGISTER_ID}\",   \"zaken_schema\":\"zaak\",
	      \"taken_source\":\"openregister\",   \"taken_register\":\"${REGISTER_ID}\",   \"taken_schema\":\"taak\",
	      \"klanten_source\":\"openregister\", \"klanten_register\":\"${REGISTER_ID}\", \"klanten_schema\":\"klant\"
	    }" \
	"${BASE}/index.php/apps/zaakafhandelapp/settings" || echo 000)"
echo "[newman-seed] POST /settings -> HTTP ${SET_CODE}"
head -c 600 "$SET_BODY"; echo
if [ "$SET_CODE" != "200" ]; then
	echo "::error::POST /apps/zaakafhandelapp/settings returned ${SET_CODE}. Without these keys every zaken/taken/klanten request 500s with 'Unknown object type'."
	exit 1
fi

# ── 4. Verify against the app, not against the writer ────────────────────────
# `SettingsController::create()` echoes back what it stored, so a 200 above only
# proves appconfig accepted the strings. What matters is whether the app can now
# resolve a mapper — and read and write are DIFFERENT code paths through
# OpenRegister (a slug passes the first and fails the second, which is the whole
# reason step 2 exists). So exercise both, per object type, and delete what the
# write created so the collection starts from the state it expects.
fail=0
for pair in "zrc/zaken:omschrijving" "taken:titel" "klanten:naam"; do
	path="${pair%%:*}"
	field="${pair##*:}"

	read_code="$(curl -sS -o /dev/null -w '%{http_code}' \
		-u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
		"${BASE}/index.php/apps/zaakafhandelapp/api/${path}" || echo 000)"

	write_body="$(mktemp)"
	write_code="$(curl -sS -o "$write_body" -w '%{http_code}' \
		-u "${USER_NAME}:${USER_PASS}" \
		-X POST \
		-H 'OCS-APIRequest: true' -H 'Content-Type: application/json' \
		-d "{\"${field}\":\"newman-seed-probe\"}" \
		"${BASE}/index.php/apps/zaakafhandelapp/api/${path}" || echo 000)"

	echo "[newman-seed] ${path}: GET -> ${read_code}, POST -> ${write_code}"

	if [ "$read_code" != "200" ] || [ "$write_code" != "200" ]; then
		echo "::error::${path} is not usable after seeding (GET ${read_code}, POST ${write_code}). First 300 bytes of the write response:"
		head -c 300 "$write_body"; echo
		fail=1
		continue
	fi

	# Clean the probe up again — the collection asserts on totals it seeds itself.
	probe_id="$(python3 -c "import json,sys; print(json.load(open(sys.argv[1])).get('id',''))" "$write_body" 2>/dev/null || true)"
	if [ -n "$probe_id" ]; then
		curl -sS -o /dev/null -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
			-X DELETE "${BASE}/index.php/apps/zaakafhandelapp/api/${path}/${probe_id}" || true
	fi
done

if [ "$fail" -ne 0 ]; then
	echo "::error::Seed verification failed. Running the collection now would produce 500s and JSONErrors that read like app bugs."
	exit 1
fi

echo "[newman-seed] zaken / taken / klanten bound to register ${REGISTER_ID}; read and write both verified."
echo "[newman-seed] done."
