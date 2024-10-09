/* eslint-disable no-console */
// The store script handles app whide variables (or state), for the use of these variables and there governing concepts read the design.md
import { reactive } from 'vue'

export const store = reactive({
	rolId: false,
	rolItem: false,
	setRolId(rolId) {
		this.rolId = rolId
		console.log('Active rol ID set to ' + rolId)
	},
	setRolItem(rolItem) {
		this.rolItem = rolItem
		console.log('Active rol item set to ' + rolItem)
	},
})
