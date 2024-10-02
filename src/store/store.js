/* eslint-disable no-console */
// The store script handles app wide variables (or state), for the use of these variables and there governing concepts read the design.md
import pinia from '../pinia.js'
import { useNavigationStore } from './modules/navigation.js'
import { useZaakStore } from './modules/zaken.js'

const navigationStore = useNavigationStore(pinia)
const zaakStore = useZaakStore(pinia)

export {
	// generic
	navigationStore,
	zaakStore,
}
