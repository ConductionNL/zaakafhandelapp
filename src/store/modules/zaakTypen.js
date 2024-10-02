/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { ZaakType } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen'

export const useZaakTypenStore = defineStore('zaakTypen', {
	state: () => ({
		zaakTypeItem: false,
		zaakTypeList: [],
	}),
	actions: {
		setZaakTypeItem(zaakTypeItem) {
			this.zaakTypeItem = zaakTypeItem && new ZaakType(zaakTypeItem)
			console.log('Active zaaktype item set to ' + zaakTypeItem)
		},
		setZaakTypeList(zaakTypeList) {
			this.zaakTypeList = zaakTypeList.map(
			    (zaakTypeItem) => new ZaakType(zaakTypeItem),
			)
			console.log('Zaaktypen list set to ' + zaakTypeList.length + ' items')
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshZaakTypeList(search = null) {
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
			const entities = data.map((zaakTypeItem) => new ZaakType(zaakTypeItem))

			this.setZaakTypeList(data)

			return { response, data, entities }
		},
		// New function to get a single zaaktype
		async getZaakType(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entity = new ZaakType(data)

			this.setZaakTypeItem(data)

			return { response, data, entity }
		},
		// Delete a zaaktype
		async deleteZaakType(zaakTypeItem) {
			if (!zaakTypeItem.id) {
				throw new Error('No zaaktype item to delete')
			}

			const endpoint = `${apiEndpoint}/${zaakTypeItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()

			this.refreshZaakTypeList()

			return { response, data }
		},
		// Create or save a zaaktype from store
		async saveZaakType(zaakTypeItem) {
			if (!zaakTypeItem) {
				throw new Error('No zaaktype item to save')
			}

			const isNewZaakType = !zaakTypeItem.id
			const endpoint = isNewZaakType
				? `${apiEndpoint}`
				: `${apiEndpoint}/${zaakTypeItem.id}`
			const method = isNewZaakType ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(zaakTypeItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entity = new ZaakType(data)

			this.setZaakTypeItem(data)
			this.refreshZaakTypeList()

			return { response, data, entity }
		},
	},
})
