/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Zaak } from '../../entities/index.js'

const apiEndpoint = 'index.php/apps/zaakafhandelapp/api/zrc/zaken'

export const useZaakStore = defineStore('zaken', {
	state: () => ({
		zaakItem: false,
		zakenList: [],
	}),
	actions: {
		setZaakItem(zaakItem) {
			this.zaakItem = zaakItem && new Zaak(zaakItem)
			console.log('Active zaak item set to ' + zaakItem)
		},
		setZakenList(zakenList) {
			this.zakenList = zakenList.map(
			    (zaakItem) => new Zaak(zaakItem),
			)
			console.log('Zaken list set to ' + zakenList.length + ' items')
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshZakenList(search = null) {
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
			const entities = data.map((zaakItem) => new Zaak(zaakItem))

			this.setZakenList(data)

			return { response, data, entities }
		},
		// New function to get a single zaak
		async getZaak(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entity = new Zaak(data)

			this.setZaakItem(data)

			return { response, data, entity }
		},
		// Delete a zaak
		async deleteZaak(zaakItem) {
			if (!zaakItem.id) {
				throw new Error('No zaak item to delete')
			}

			const endpoint = `${apiEndpoint}/${zaakItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()

			this.refreshZakenList()

			return { response, data }
		},
		// Create or save a zaak from store
		async saveZaak(zaakItem) {
			if (!zaakItem) {
				throw new Error('No zaak item to save')
			}

			const isNewZaak = !zaakItem.id
			const endpoint = isNewZaak
				? `${apiEndpoint}`
				: `${apiEndpoint}/${zaakItem.id}`
			const method = isNewZaak ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(zaakItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entity = new Zaak(data)

			this.setZaakItem(data)
			this.refreshZakenList()

			return { response, data, entity }
		},
	},
})
