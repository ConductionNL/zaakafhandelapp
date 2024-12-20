/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Bericht } from '../../entities/index.js'
import router from '../../router/router.ts'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/berichten'

export const useBerichtStore = defineStore('berichten', {
	state: () => ({
		berichtItem: false,
		berichtenList: [],
		auditTrailItem: null,
	}),
	actions: {
		setBerichtItem(berichtItem) {
			this.berichtItem = berichtItem && new Bericht(berichtItem)
			console.log('Active bericht item set to ' + berichtItem)
		},
		setBerichtenList(berichtenList) {
			this.berichtenList = berichtenList.map(
			    (berichtItem) => new Bericht(berichtItem),
			)
			console.log('Berichten list set to ' + berichtenList.length + ' items')
		},
		setAuditTrailItem(auditTrailItem) {
			this.auditTrailItem = auditTrailItem
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshBerichtenList(search = null) {
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
			const entities = data.map((berichtItem) => new Bericht(berichtItem))

			this.setBerichtenList(data)

			return { response, data, entities }
		},
		// New function to get a single bericht
		async getBericht(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Bericht(data)

			this.setBerichtItem(data)

			return { response, data, entity }
		},
		// Delete a bericht
		async deleteBericht(berichtItem) {
			if (!berichtItem.id) {
				throw new Error('No bericht item to delete')
			}

			const endpoint = `${apiEndpoint}/${berichtItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshBerichtenList()
			router.push({ name: 'dynamic-view', params: { view: 'berichten' } })

			return { response }
		},
		// Create or save a bericht from store
		async saveBericht(berichtItem) {
			if (!berichtItem) {
				throw new Error('No bericht item to save')
			}

			const isNewBericht = !berichtItem.id
			const endpoint = isNewBericht
				? `${apiEndpoint}`
				: `${apiEndpoint}/${berichtItem.id}`
			const method = isNewBericht ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(berichtItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Bericht(data)

			this.setBerichtItem(data)
			this.refreshBerichtenList()
			router.push({ name: 'dynamic-view', params: { view: 'berichten', id: entity.id } })

			return { response, data, entity }
		},
	},
})
