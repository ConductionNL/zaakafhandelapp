/* eslint-disable no-console */
// The store script handles app wide variables (or state), for the use of these variables and there governing concepts read the design.md
import pinia from '../pinia.js'
import { useNavigationStore } from './modules/navigation.js'
import { useZaakStore } from './modules/zaken.js'
import { useZaakTypeStore } from './modules/zaakTypen.js'
import { useKlantStore } from './modules/klanten.js'
import { useTaakStore } from './modules/taak.js'

const navigationStore = useNavigationStore(pinia)
const zaakStore = useZaakStore(pinia)
const zaakTypeStore = useZaakTypeStore(pinia)
const klantStore = useKlantStore(pinia)
const taakStore = useTaakStore(pinia)

export {
	// generic
	navigationStore,
	zaakStore,
	zaakTypeStore,
	klantStore,
	taakStore,
}
