// The store script handles app wide variables (or state), for the use of these variables and there governing concepts read the design.md
import pinia from '../pinia.js'
import { useBerichtStore } from './modules/berichten.js'
import { useKlantStore } from './modules/klanten.js'
import { useNavigationStore } from './modules/navigation.ts'
import { useRolStore } from './modules/rol.js'
import { useTaakStore } from './modules/taak.js'
import { useZaakStore } from './modules/zaken.ts'
import { useZaakTypeStore } from './modules/zaakTypen.ts'

const berichtStore = useBerichtStore(pinia)
const klantStore = useKlantStore(pinia)
const navigationStore = useNavigationStore(pinia)
const rolStore = useRolStore(pinia)
const taakStore = useTaakStore(pinia)
const zaakStore = useZaakStore(pinia)
const zaakTypeStore = useZaakTypeStore(pinia)

export {
	berichtStore,
	klantStore,
	navigationStore,
	rolStore,
	taakStore,
	zaakStore,
	zaakTypeStore,
}
