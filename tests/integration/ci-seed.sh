#!/usr/bin/env bash
#
# SPDX-FileCopyrightText: 2026 Conduction B.V.
# SPDX-License-Identifier: EUPL-1.2
#
# Provision zaakafhandelapp for the shared `Integration Tests (Newman)` CI job.
#
# Wired up as the workflow's `newman-seed-command`. That step runs AFTER
# `php -S` is up and with cwd set to the Nextcloud server root, so this is
# invoked as:
#
#     newman-seed-command: 'bash apps/zaakafhandelapp/tests/integration/ci-seed.sh'
#
# WHY THIS IS NEEDED
# ------------------
# The Newman job had no seed at all, and the collection cannot create its own
# preconditions. Two separate things were missing, and both were visible in the
# 2026-08-11 run once the server was finally one OpenRegister can run on
# (#338 — before that the whole job was measuring a box with no OpenRegister):
#
#   1. NO REGISTER. The collection's first request resolves the register by
#      slug:
#
#        GET /apps/openregister/api/registers?_limit=300  [200 OK]
#        1. zaakafhandelapp register resolved by slug     <- FAILED
#
#      The list is reachable; the register is simply not there. `occ app:enable
#      zaakafhandelapp` provisions nothing (no lib/Settings/*_register.json, no
#      IRepairStep, no import route — see tests/e2e/ci-seed.sh). So
#      {{zaakRegister}} kept its dev-box default of 239 and every object call
#      went to /api/objects/239/zaak -> 404.
#
#   2. NO SOURCE CONFIG. Even with the register present, every ZGW route 500s:
#
#        InvalidArgumentException: Unknown object type: zaken
#          at lib/Service/ObjectMapperService.php:66
#
#      getMapper() reads `<type>_source` from app config, defaults it to
#      'internal', and its match() has only a `default => throw` arm. Nothing
#      but an explicit `<type>_source = openregister` makes a type resolvable.
#      The collection writes exactly one key (`zaken_register`) and no _source
#      at all, so it cannot bootstrap itself.
#
# Together those accounted for 17 of 55 failing assertions.
#
# Section 1 delegates to the Playwright seed for the register+schema import, so
# there is ONE import path and ONE payload (tests/e2e/ci-register.json).
# ZAA_SEED_SKIP_SPA=1 stops it from also warming the SPA and gating on the
# webpack bundle — correct for Playwright, meaningless here, and this job never
# builds a frontend.
#
# Section 2 resolves the numeric register and schema ids and writes the app
# config through the app's own settings API.
#
# Section 3 verifies against the app's own endpoint, because sections 1 and 2
# can both report success and still leave every route 500ing.
#
# It is idempotent: the importer matches by slug and updates in place, and the
# settings write is a plain overwrite.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# ── Target resolution ────────────────────────────────────────────────────────
# Same contract as tests/e2e/ci-seed.sh, including its refusal to default to
# localhost:8080 off CI: that address is the SHARED dev container and this
# script performs ADMIN WRITES to app config.
BASE="${PLAYWRIGHT_BASE_URL:-${BASE_URL:-${NEXTCLOUD_URL:-${NC_BASE_URL:-}}}}"
if [ -z "$BASE" ]; then
	if [ "${GITHUB_ACTIONS:-}" = "true" ] || [ "${CI:-}" = "true" ]; then
		BASE="http://localhost:8080"
	else
		echo "ERROR: no base URL set. Export BASE_URL or NEXTCLOUD_URL." >&2
		echo "       Refusing to default to http://localhost:8080 outside CI —" >&2
		echo "       that is the SHARED dev container and this script writes app config." >&2
		exit 1
	fi
fi
BASE="${BASE%/}"

USER_NAME="${ADMIN_USER:-${NC_ADMIN_USER:-admin}}"
USER_PASS="${ADMIN_PASSWORD:-${NC_ADMIN_PASS:-admin}}"

echo "[newman-seed] target: ${BASE}"

# ── 1. Register + schemas ────────────────────────────────────────────────────
echo "[newman-seed] provisioning register via tests/e2e/ci-seed.sh (SPA warm-up skipped)"
ZAA_SEED_SKIP_SPA=1 bash "${APP_ROOT}/e2e/ci-seed.sh"

# ── 2. Point every object type at the register ───────────────────────────────
# The app keys config by its own plural type name; OpenRegister keys schemas by
# the singular slug shipped in ci-register.json. The mapping is not derivable
# (statusen/status, zaaktypen/zaaktype, rollen/rol), so it is written out.
# Names on the left MUST match SettingsController::WRITABLE_KEYS — a key that
# is not on that allow-list is silently ignored, which would look exactly like
# a successful write.
TYPE_TO_SLUG="
zaken:zaak
taken:taak
klanten:klant
statusen:status
zaaktypen:zaaktype
medewerkers:medewerker
berichten:bericht
besluiten:besluit
resultaten:resultaat
rollen:rol
contactmomenten:contactmoment
documenten:document
"

REG_BODY="$(mktemp)"
curl -sS -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/registers?_limit=300" -o "$REG_BODY"

SCH_BODY="$(mktemp)"
curl -sS -u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
	"${BASE}/index.php/apps/openregister/api/schemas?_limit=1000" -o "$SCH_BODY"

# Build the settings payload. Numeric ids, not slugs: getOpenRegisterMapper()
# passes the stored value straight to OpenRegister's getMapper(register:,
# schema:), and the numeric id is the form the collection's own preflight
# resolves and the form the dev instance stores.
SETTINGS_JSON="$(mktemp)"
python3 - "$REG_BODY" "$SCH_BODY" "$SETTINGS_JSON" "$TYPE_TO_SLUG" <<'PY'
import json, sys

reg_path, sch_path, out_path, mapping_raw = sys.argv[1:5]


def items(path, kind):
    with open(path) as fh:
        raw = fh.read()
    try:
        body = json.loads(raw)
    except json.JSONDecodeError:
        print(f'::error::{kind} endpoint did not return JSON. First 500 bytes:')
        print(raw[:500])
        sys.exit(1)
    return body if isinstance(body, list) else body.get('results', [])


registers = items(reg_path, 'registers')
schemas = items(sch_path, 'schemas')

register = next((r for r in registers if r.get('slug') == 'zaakafhandelapp'), None)
if register is None:
    print("::error::No register with slug 'zaakafhandelapp' after import.")
    print('  present:', sorted(str(r.get('slug')) for r in registers))
    sys.exit(1)

by_slug = {s.get('slug'): s.get('id') for s in schemas if isinstance(s, dict)}

payload = {}
missing = []
for line in mapping_raw.strip().splitlines():
    obj_type, slug = line.strip().split(':')
    schema_id = by_slug.get(slug)
    if schema_id is None:
        missing.append(slug)
        continue
    payload[f'{obj_type}_source'] = 'openregister'
    payload[f'{obj_type}_register'] = str(register['id'])
    payload[f'{obj_type}_schema'] = str(schema_id)

if missing:
    print(f'::error::schema slug(s) missing after import: {missing}')
    print('  present:', sorted(s for s in by_slug if s))
    sys.exit(1)

with open(out_path, 'w') as fh:
    json.dump(payload, fh)

print(f"[newman-seed] register 'zaakafhandelapp' -> id {register['id']}")
print(f'[newman-seed] mapping {len(payload) // 3} object type(s) to openregister')
PY

SET_URL="${BASE}/index.php/apps/zaakafhandelapp/api/settings"
SET_BODY="$(mktemp)"
SET_CODE="$(
	curl -sS -o "$SET_BODY" -w '%{http_code}' \
		-u "${USER_NAME}:${USER_PASS}" \
		-X POST \
		-H 'OCS-APIRequest: true' \
		-H 'Content-Type: application/json' \
		--data-binary "@${SETTINGS_JSON}" \
		"$SET_URL" || echo 000
)"
echo "[newman-seed] POST ${SET_URL} -> HTTP ${SET_CODE}"

if [ "$SET_CODE" != "200" ]; then
	echo "::error::Writing zaakafhandelapp settings failed (HTTP ${SET_CODE}). Every ZGW route resolves its mapper from these keys."
	head -c 2000 "$SET_BODY"; echo
	exit 1
fi

# SettingsController::create() ignores unknown keys SILENTLY and echoes back
# only what it accepted — so the response body, not the status code, is the
# evidence that the write landed.
python3 - "$SET_BODY" "$SETTINGS_JSON" <<'PY'
import json, sys

with open(sys.argv[1]) as fh:
    written = json.load(fh)
with open(sys.argv[2]) as fh:
    intended = json.load(fh)

missing = [k for k in intended if k not in written]
wrong = [k for k in intended if k in written and str(written[k]) != str(intended[k])]

if missing or wrong:
    print(f'::error::settings write did not take. missing={missing} wrong={wrong}')
    print('  NOTE: SettingsController::WRITABLE_KEYS is an allow-list; an unlisted key is dropped without error.')
    sys.exit(1)

print(f'[newman-seed] settings verified: {len(intended)} key(s) persisted')
PY

# ── 3. Verify through the app's own endpoint ─────────────────────────────────
# Sections 1 and 2 can both pass and still leave every route 500ing — a schema
# id that exists but is not in this register resolves to a mapper that throws
# on first use. The only honest check is the endpoint the collection calls.
ZAKEN_BODY="$(mktemp)"
ZAKEN_CODE="$(
	curl -sS -o "$ZAKEN_BODY" -w '%{http_code}' \
		-u "${USER_NAME}:${USER_PASS}" -H 'OCS-APIRequest: true' \
		"${BASE}/index.php/apps/zaakafhandelapp/api/zrc/zaken?_limit=1" || echo 000
)"
echo "[newman-seed] GET api/zrc/zaken -> ${ZAKEN_CODE}"

if [ "$ZAKEN_CODE" != "200" ]; then
	echo "::error::GET /apps/zaakafhandelapp/api/zrc/zaken returned ${ZAKEN_CODE} after seeding."
	echo "::error::This is the exact call the collection's 'zaken index 200' assertion makes."
	head -c 2000 "$ZAKEN_BODY"; echo
	exit 1
fi

echo "[newman-seed] done."
