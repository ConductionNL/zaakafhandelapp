// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Store barrel — Phase 1 of the @conduction/nextcloud-vue store migration
// (openspec/changes/zaakafhandelapp-store-migration). The lib's
// `useObjectStore` is added side-by-side with the existing thirteen
// legacy controller-backed stores; the legacy stores stay intact for
// every existing Vue consumer, and the lib store is pre-configured with
// the eleven manifest-declared object types so manifest pages and lib
// sub-resource plugins (live updates, audit trails, files, relations)
// have a known-shape store to bind against.
//
// See openspec/changes/zaakafhandelapp-store-migration/design.md for the
// pattern rationale and the Phase 2 cutover triggers.

import { generateUrl } from '@nextcloud/router'
import { useObjectStore } from '@conduction/nextcloud-vue'
import pinia from '../pinia.js'
import { useNavigationStore } from './modules/navigation.ts'
import { useZaakStore } from './modules/zaken.ts'
import { useZaakTypeStore } from './modules/zaakTypen.ts'
import { useBerichtStore } from './modules/berichten.js'
import { useKlantStore } from './modules/klanten.js'
import { useRolStore } from './modules/rol.ts'
import { useTaakStore } from './modules/taak.js'
import { useSearchStore } from './modules/search.ts'
import { useContactMomentStore } from './modules/contactmoment.ts'
import { useMedewerkerStore } from './modules/medewerkers.js'
import { useResultaatStore } from './modules/resultaten.ts'
import { useBesluitStore } from './modules/besluiten.ts'
import { useDocumentStore } from './modules/documenten.ts'

// Legacy controller-backed stores — preserved verbatim for every existing
// Vue consumer. These talk to flat in-app PHP controllers and do NOT match
// the OR canonical shape; replacing them is Phase 2 work, gated on the
// triggers listed in the change's design.md.
const berichtStore = useBerichtStore(pinia)
const klantStore = useKlantStore(pinia)
const navigationStore = useNavigationStore(pinia)
const rolStore = useRolStore(pinia)
const taakStore = useTaakStore(pinia)
const zaakStore = useZaakStore(pinia)
const zaakTypeStore = useZaakTypeStore(pinia)
const searchStore = useSearchStore(pinia)
const contactMomentStore = useContactMomentStore(pinia)
const medewerkerStore = useMedewerkerStore(pinia)
const resultaatStore = useResultaatStore(pinia)
const besluitStore = useBesluitStore(pinia)
const documentStore = useDocumentStore(pinia)

// Manifest-declared object types — slugs verbatim from src/manifest.json.
// Schema slug equals the type slug; register slug is the app id.
const REGISTER_SLUG = 'zaakafhandelapp'
const OBJECT_TYPES = Object.freeze([
	'zaak',
	'taak',
	'klant',
	'medewerker',
	'contactmoment',
	'bericht',
	'rol',
	'zaaktype',
	'besluit',
	'resultaat',
	'document',
])

let initialized = false

/**
 * Boot the lib's useObjectStore for zaakafhandelapp.
 *
 * Configures the lib store with the canonical OR base URL and registers
 * the eleven manifest-declared object types. Idempotent: subsequent calls
 * short-circuit. Synchronous in Phase 1 (no fetches); kept async for
 * Phase 2 forward-compatibility, where settings + per-tenant register
 * resolution will live here.
 *
 * @return {Promise<ReturnType<typeof useObjectStore>>} The configured lib store.
 */
export async function initializeStores() {
	const objectStore = useObjectStore(pinia)

	if (initialized) {
		return objectStore
	}

	objectStore.configure({
		baseUrl: generateUrl('/apps/openregister/api/objects'),
	})

	for (const slug of OBJECT_TYPES) {
		objectStore.registerObjectType(slug, slug, REGISTER_SLUG)
	}

	initialized = true
	return objectStore
}

export {
	// Lib store — adopt for new code, manifest pages, and any consumer
	// needing the lib's sub-resource plugins.
	useObjectStore,
	// Legacy controller-backed stores — preserved for Phase 1 compatibility.
	berichtStore,
	klantStore,
	navigationStore,
	rolStore,
	taakStore,
	zaakStore,
	zaakTypeStore,
	searchStore,
	contactMomentStore,
	medewerkerStore,
	resultaatStore,
	besluitStore,
	documentStore,
}
