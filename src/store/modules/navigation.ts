/* eslint-disable no-console */
import { defineStore } from 'pinia'

interface NavigationStoreState {
    selected: 'dashboard' | 'berichten' | 'klanten' | 'rollen' | 'taken' | 'zaken' | 'zaakTypen' | 'search' | 'auditTrail' | 'contactMomenten' | 'medewerkers';
    modal: 'zaakForm' | 'viewZaakAuditTrail' | 'widgetZaakForm' | 'addBerichtToZaak' | 'addTaakToZaak' | 'addRolToZaak' | 'contactMomentenForm' | 'deleteContactMoment' | 'zaaktypeForm' | 'deleteZaaktype' | 'viewKlantAuditTrail' | 'viewBerichtAuditTrail' | 'editTaak' | 'viewTaakAuditTrail' | 'viewKlantRegister' | 'editMedewerker' | 'resultaatForm' | 'deleteResultaat' | 'besluitForm' | 'deleteBesluit' | null;
    viewModal: 'viewContactMoment';
    dialog: string;
    transferData: string;
}

export const useNavigationStore = defineStore('ui', {
	state: () => ({
		// The currently active menu item, defaults to '' which triggers the dashboard
		selected: 'dashboard',
		// The currently active modal, managed trough the state to ensure that only one modal can be active at the same time
		modal: null,
		// The currently active view modal, managed trough the state to ensure that only one view modal can be active at the same time
		viewModal: null,
		// The currently active dialog
		dialog: null,
		// Any data needed in various models, dialogs, views which cannot be transferred through normal means or without writing bad/excessive code
		transferData: null,
	} as NavigationStoreState),
	actions: {
		setSelected(selected: NavigationStoreState['selected']) {
			this.selected = selected
			console.log('Active menu item set to ' + selected)
		},
		setModal(modal: NavigationStoreState['modal']) {
			this.modal = modal
			console.log('Active modal set to ' + modal)
		},
		setViewModal(viewModal: NavigationStoreState['viewModal']) {
			this.viewModal = viewModal
			console.log('Active view modal set to ' + viewModal)
		},
		setDialog(dialog: NavigationStoreState['dialog']) {
			this.dialog = dialog
			console.log('Active dialog set to ' + dialog)
		},
		setTransferData(data: NavigationStoreState['transferData']) {
			this.transferData = data
		},
		getTransferData(): NavigationStoreState['transferData'] {
			const tempData = this.transferData
			this.transferData = null
			return tempData
		},
	},
})
