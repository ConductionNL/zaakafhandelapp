// The store script handles app wide variables (or state), for the use of these variables and there governing concepts read the design.md
import pinia from '../pinia.js'
import { useObjectStore } from './modules/object.ts'
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

const objectStore = useObjectStore(pinia)
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

export {
	objectStore,
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
