/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { Rol } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/zrc/rollen'

export const useRolStore = defineStore('rollen', {
	state: () => ({
		rolItem: false,
		rollenList: [],
	}),
	actions: {
		setRolItem(rolItem) {
			this.rolItem = rolItem && new Rol(rolItem)
			console.log('Active rol item set to ' + rolItem)
		},
		setRollenList(rollenList) {
			this.rollenList = rollenList.map(
			    (rolItem) => new Rol(rolItem),
			)
			console.log('Rollen list set to ' + rollenList.length + ' items')
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshRollenList(search = null) {
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
			const entities = data.map((rolItem) => new Rol(rolItem))

			this.setRollenList(data)

			return { response, data, entities }
		},
		// New function to get a single rol
		async getRol(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Rol(data)

			this.setRolItem(data)

			return { response, data, entity }
		},
		// Delete a rol
		async deleteRol(rolItem) {
			if (!rolItem.id) {
				throw new Error('No rol item to delete')
			}

			const endpoint = `${apiEndpoint}/${rolItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshRollenList()

			return { response }
		},
		// Create or save a rol from store
		async saveRol(rolItem) {
			if (!rolItem) {
				throw new Error('No rol item to save')
			}

			const isNewRol = !rolItem.id
			const endpoint = isNewRol
				? `${apiEndpoint}`
				: `${apiEndpoint}/${rolItem.id}`
			const method = isNewRol ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(rolItem),
				},
			)

			if (!response.ok) {
				console.log(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Rol(data)

			this.setRolItem(data)
			this.refreshRollenList()

			return { response, data, entity }
		},
	},
})
