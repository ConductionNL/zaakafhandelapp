/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Taak } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/taken'

export const useTaakStore = defineStore('taken', {
	state: () => ({
		taakItem: false,
		takenList: [],
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
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshTakenList(search = null) {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
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

			return { response }
		},
		// Create or save a taak from store
		async saveTaak(taakItem) {
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
			this.refreshTakenList()

			return { response, data, entity }
		},
	},
})