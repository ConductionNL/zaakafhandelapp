/* eslint-disable no-console */
// The store script handles app whide variables (or state), for the use of these variables and there governing concepts read the design.md
import { reactive } from 'vue'

export const store = reactive({
	// The curently active menu item, defaults to '' wich triggers the dashboard
	selected: 'dashboard',
	search: '',
	// The currently active modal, managed trought the state to ensure that only one modal can be active at the same time
	modal: false,
	modalData: [], // optional data to pass to the modal
	// The curently active item (or object) , managed trought the state to ensure that only one modal can be active at the same time
	zaakItem: false,
	zaakTypeItem: false,
	klantItem: false,
	taakItem: false,
	taakZaakId: false,
	berichtItem: false,
	rolItem: false,
	// Lets add some setters
	setSelected(selected) { // The general state for menu tiemts
		this.selected = selected
		console.log('Active menu item set to ' + selected)
	},
	setModal(modal) { // The general state for modals
		this.modal = modal
		console.log('Active modal item set to ' + modal)
	},
	setDialog(modal) { // The general state for dialogs
		this.modal = modal
		console.log('Active modal item set to ' + modal)
	},
	// The Bussens logic for zaken
	setZaakItem(zaakItem) {
		const zaakDefault = {
			identificatie: '',
			omschrijving: '',
			bronorganisatie: '',
			verantwoordelijkeOrganisatie: '',
			startdatum: '',
			archiefstatus: '',
			registratiedatum: '',
			toelichting: '',
		}
		this.zaakItem = { ...zaakDefault, ...zaakItem }
		console.log('Active zaak item set to ' + zaakItem)
	},
	// The Bussens logic for zaaktypen
	setZaakTypeItem(zaakTypeItem) {
		const zaakTypeItemDefault = {
			identificatie: '',
			omschrijving: '',
			omschrijvingGeneriek: '',
			vertrouwelijkheidaanduiding: '',
			doel: '',
			aanleiding: '',
			toelichting: '',
			indicatieInternOfExtern: '',
			handelingInitiator: '',
			onderwerp: '',
			handelingBehandelaar: '',
			doorlooptijd: '',
			servicenorm: '',
			opschortingEnAanhoudingMogelijk: '',
			verlengingMogelijk: '',
			verlengingstermijn: '',
			trefwoorden: [],
			publicatieIndicatie: '',
			publicatietekst: '',
			verantwoordingsrelatie: '',
			productenOfDiensten: '',
			selectielijstProcestype: '',
			referentieproces: '',
			verantwoordelijke: '',
			broncatalogus: '',
			catalogus: '',
			besluittypen: '',
			deelzaaktypen: '',
			gerelateerdeZaaktypen: '',
			beginGeldigheid: '',
			eindeGeldigheid: '',
			beginObject: '',
			eindeObject: '',
			versiedatum: '',
		}
		this.zaakTypeItem = { ...zaakTypeItemDefault, ...zaakTypeItem }
		console.log('Active zaak type item set to ' + zaakTypeItem)
	},
	// The Bussens logic for klanten
	setKlantItem(klantItem) {
		this.klantItem = klantItem
		console.log('Active klant item set to ' + klantItem)
	},
	// The Bussens logic for taken
	setTaakItem(taakItem) {
		this.taakItem = taakItem
		console.log('Active taak item set to ' + taakItem)
	},
	// The Bussens logic for berichten
	setBerichtItem(berichtItem) {
		this.berichtItem = berichtItem
		console.log('Active bericht item set to ' + berichtItem)
	},
	// The Bussens logic for rollen
	setRolItem(rolItem) {
		this.rolItem = rolItem
		console.log('Active rol item set to ' + rolItem)
	},
})
