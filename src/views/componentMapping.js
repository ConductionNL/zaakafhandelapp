import BerichtenIndex from './berichten/BerichtenIndex.vue'
import BesluitenIndex from './besluiten/BesluitenIndex.vue'
import SearchIndex from './search/SearchIndex.vue'
import DocumentenIndex from './documenten/DocumentenIndex.vue'
import KlantenIndex from './klanten/KlantenIndex.vue'
import MedewerkerIndex from './medewerkers/MedewerkerIndex.vue'
import ResultatenIndex from './resultaten/ResultatenIndex.vue'
import RollenIndex from './rollen/RollenIndex.vue'
import StatusssenIndex from './statussen/StatussenIndex.vue'
import TakenIndex from './taken/TakenIndex.vue'
import ZaakTypenIndex from './zaakTypen/ZakenTypenIndex.vue'
import ZakenIndex from './zaken/ZakenIndex.vue'
import ContactMomentenIndex from './contactMomenten/ContactMomentenIndex.vue'

// the keys in this object are the names of the routes in the router
// a.k.a. will be used in the url `/klanten/id`
export const viewComponents = {
	berichten: BerichtenIndex,
	besluiten: BesluitenIndex,
	search: SearchIndex,
	documenten: DocumentenIndex,
	klanten: KlantenIndex,
	medewerkers: MedewerkerIndex,
	resultaten: ResultatenIndex,
	rollen: RollenIndex,
	statussen: StatusssenIndex,
	taken: TakenIndex,
	zaakTypen: ZaakTypenIndex,
	zaken: ZakenIndex,
	contactmomenten: ContactMomentenIndex,
}
