/* eslint-disable no-console */
// The store script handles app whide variables (or state), for the use of these variables and there governing concepts read the design.md
import { reactive } from 'vue'

export const store = reactive({
	taakItem: false,
	taakId: false,
	taakZaakId: false,
	berichtId: false,
	berichtItem: false,
	rolId: false,
	rolItem: false,
	setTaakItem(taakItem) {
		this.taakItem = taakItem
		console.log('Active taak item set to ' + taakItem)
	},
	setTaakId(taakId) {
		this.taakId = taakId
		console.log('Active taak id set to ' + taakId)
	},
	setBerichtId(berichtId) {
		this.berichtId = berichtId
		console.log('Active bericht ID set to ' + berichtId)
	},
	setBerichtItem(berichtItem) {
		this.berichtItem = berichtItem
		console.log('Active bericht item set to ' + berichtItem)
	},
	setRolId(rolId) {
		this.rolId = rolId
		console.log('Active rol ID set to ' + rolId)
	},
	setRolItem(rolItem) {
		this.rolItem = rolItem
		console.log('Active rol item set to ' + rolItem)
	},
})
