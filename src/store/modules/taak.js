/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Taak } from '../../entities/index.js'
import router from '../../router/router.ts'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/taken'

export const useTaakStore = defineStore('taken', {
	state: () => ({
		taakItem: false,
		takenList: [],
		taakZaakId: null,
		auditTrailItem: null,
		widgetTaakId: null,
	}),
	actions: {
		setTaakItem(taakItem) {
			this.taakItem = taakItem && new Taak(taakItem)
			console.log('Active taak item set to ' + taakItem)
		},
		setTakenList(takenList) {
			this.takenList = takenList.map(
				(taakItem) => new Taak(taakItem),
			)
			console.log('Taken list set to ' + takenList.length + ' items')
		},
		setTaakZaakId(taakZaakId) {
			this.taakZaakId = taakZaakId
			console.log('Active taak Zaak Id set to ' + taakZaakId)
		},
		setAuditTrailItem(auditTrailItem) {
			this.auditTrailItem = auditTrailItem
		},
		setWidgetTaakId(widgetTaakId) {
			this.widgetTaakId = widgetTaakId
			console.log('Active widget taak Id set to ' + widgetTaakId)
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshTakenList(search = null, notClosed = false, user = null) {
			let endpoint = apiEndpoint

			const params = new URLSearchParams()
			if (search) {
				params.append('_search', search)
			}
			if (notClosed) {
				params.append('status', 'open')
			}
			if (user) {
				params.append('medewerker', user)
			}

			if (params.toString()) {
				endpoint += `?${params.toString()}`
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entities = data.map((taakItem) => new Taak(taakItem))

			this.setTakenList(data)

			return { response, data, entities }
		},
		// Function to get a single taak
		async getTaak(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Taak(data)

			this.setTaakItem(data)

			return { response, data, entity }
		},
		// Delete a taak
		async deleteTaak(taakItem) {
			if (!taakItem.id) {
				throw new Error('No taak item to delete')
			}

			const endpoint = `${apiEndpoint}/${taakItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshTakenList()
			// go back to taken list
			router.replace({ name: 'dynamic-view', params: { view: 'taken' } })

			return { response }
		},
		// Create or save a taak from store
		async saveTaak(taakItem, options = {}) {
			if (!taakItem) {
				throw new Error('No taak item to save')
			}

			const isNewTaak = !taakItem.id
			const endpoint = isNewTaak
				? `${apiEndpoint}`
				: `${apiEndpoint}/${taakItem.id}`
			const method = isNewTaak ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(taakItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Taak(data)

			this.setTaakItem(data)
			if (!options.doNotRefresh) {
				this.refreshTakenList()
			}
			// go to new item with this id
			router.push({ name: 'dynamic-view', params: { view: 'taken', id: entity.id } })

			return { response, data, entity }
		},
	},
})
