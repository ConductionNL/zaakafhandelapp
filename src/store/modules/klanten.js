/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Klant } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/klanten'

export const useKlantStore = defineStore('klanten', {
	state: () => ({
		klantItem: false,
		klantenList: [],
		widgetKlantId: null,
		auditTrailItem: null,
	}),
	actions: {
		setKlantItem(klantItem) {
			this.klantItem = klantItem && new Klant(klantItem)
			console.log('Active klant item set to ' + klantItem)
		},
		setWidgetKlantId(widgetKlantId) {
			this.widgetKlantId = widgetKlantId
			console.log('Widget klant id set to ' + widgetKlantId)
		},
		setKlantenList(klantenList) {
			this.klantenList = klantenList.map(
			    (klantItem) => new Klant(klantItem),
			)
			console.log('Klanten list set to ' + klantenList.length + ' items')
		},
		setAuditTrailItem(auditTrailItem) {
			this.auditTrailItem = auditTrailItem
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshKlantenList(search = null) {
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
			const entities = data.map((klantItem) => new Klant(klantItem))

			this.setKlantenList(data)

			return { response, data, entities }
		},

		async searchPersons(search = null) {
			let endpoint = apiEndpoint

			endpoint = endpoint + '?type=persoon'

			if (search !== null && search !== '') {
				endpoint = endpoint + '&voornaam=' + search
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entities = data.map((klantItem) => new Klant(klantItem))

			this.setKlantenList(data)

			return { response, data, entities }
		},

		async searchOrganisations(search = null) {
			let endpoint = apiEndpoint

			endpoint = endpoint + '?type=organisatie'

			if (search !== null && search !== '') {
				endpoint = endpoint + '&bedrijfsnaam=' + search
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entities = data.map((klantItem) => new Klant(klantItem))

			this.setKlantenList(data)

			return { response, data, entities }
		},

		// New function to get a single klant
		async getKlant(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Klant(data)

			this.setKlantItem(data)

			return { response, data, entity }
		},
		// Delete a klant
		async deleteKlant(klantItem) {
			if (!klantItem.id) {
				throw new Error('No klant item to delete')
			}

			const endpoint = `${apiEndpoint}/${klantItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshKlantenList()

			return { response }
		},
		// Create or save a klant from store
		async saveKlant(klantItem) {
			if (!klantItem) {
				throw new Error('No klant item to save')
			}

			const isNewKlant = !klantItem.id
			const endpoint = isNewKlant
				? `${apiEndpoint}`
				: `${apiEndpoint}/${klantItem.id}`
			const method = isNewKlant ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(klantItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Klant(data)

			this.setKlantItem(data)
			this.refreshKlantenList()

			return { response, data, entity }
		},
	},
})
