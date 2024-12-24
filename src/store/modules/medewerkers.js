/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Medewerker } from '../../entities/index.js'
import router from '../../router/router.ts'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/medewerkers'

export const useMedewerkerStore = defineStore('medewerkers', {
	state: () => ({
		medewerkerItem: false,
		medewerkersList: [],
		widgetMedewerkerId: null,
		auditTrailItem: null,
	}),
	actions: {
		setMedewerkerItem(medewerkerItem) {
			this.medewerkerItem = medewerkerItem && new Medewerker(medewerkerItem)
			console.log('Active medewerker item set to ' + medewerkerItem)
		},
		setWidgetMedewerkerId(widgetMedewerkerId) {
			this.widgetMedewerkerId = widgetMedewerkerId
			console.log('Widget medewerker id set to ' + widgetMedewerkerId)
		},
		setMedewerkersList(medewerkersList) {
			this.medewerkersList = medewerkersList.map(
			    (medewerkerItem) => new Medewerker(medewerkerItem),
			)
			console.log('Medewerkers list set to ' + medewerkersList.length + ' items')
		},
		setAuditTrailItem(auditTrailItem) {
			this.auditTrailItem = auditTrailItem
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshMedewerkersList(search = null) {
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
			const entities = data.map((medewerkerItem) => new Medewerker(medewerkerItem))

			this.setMedewerkersList(data)

			return { response, data, entities }
		},

		async searchMedewerkers(queryParams = null) {
			let endpoint = apiEndpoint

			if (queryParams !== null && queryParams !== '') {
				endpoint = endpoint + '?' + queryParams
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entities = data.map((medewerkerItem) => new Medewerker(medewerkerItem))

			this.setMedewerkersList(data)

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
			const entities = data.map((medewerkerItem) => new Medewerker(medewerkerItem))

			this.setMedewerkersList(data)

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
			const entities = data.map((medewerkerItem) => new Medewerker(medewerkerItem))

			this.setMedewerkersList(data)

			return { response, data, entities }
		},

		// New function to get a single medewerker
		async getMedewerker(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Medewerker(data)

			this.setMedewerkerItem(data)

			return { response, data, entity }
		},
		// Delete a medewerker
		async deleteMedewerker(medewerkerItem) {
			if (!medewerkerItem.id) {
				throw new Error('No medewerker item to delete')
			}

			const endpoint = `${apiEndpoint}/${medewerkerItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshMedewerkersList()
			router.push({ name: 'dynamic-view', params: { view: 'medewerkers' } })

			return { response }
		},
		// Create or save a medewerker from store
		async saveMedewerker(medewerkerItem) {
			if (!medewerkerItem) {
				throw new Error('No medewerker item to save')
			}

			const isNewMedewerker = !medewerkerItem.id
			const endpoint = isNewMedewerker
				? `${apiEndpoint}`
				: `${apiEndpoint}/${medewerkerItem.id}`
			const method = isNewMedewerker ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(medewerkerItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Medewerker(data)

			this.setMedewerkerItem(data)
			this.refreshMedewerkersList()
			router.push({ name: 'dynamic-view', params: { view: 'medewerkers', id: entity.id } })

			return { response, data, entity }
		},
	},
})
