/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { ContactMoment } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/contactmomenten'

export const useContactMomentStore = defineStore('contactmomenten', {
	state: () => ({
		contactMomentItem: false,
		contactMomentenList: [],
	}),
	actions: {
		setContactMomentItem(contactMomentItem) {
			this.contactMomentItem = contactMomentItem && new ContactMoment(contactMomentItem)
			console.info('Active contactmoment item set to ' + contactMomentItem)
		},
		setContactMomentenList(contactMomentenList) {
			this.contactMomentenList = contactMomentenList.map(
				(contactMomentItem) => new ContactMoment(contactMomentItem),
			)
			console.info('Contactmomenten list set to ' + contactMomentenList.length + ' items')
		},
		/* istanbul ignore next */ // ignore this for Jest until moved into a service
		async refreshContactMomentenList(search = null) {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results
			const entities = data.map((contactMomentItem) => new ContactMoment(contactMomentItem))

			this.setContactMomentenList(data)

			return { response, data, entities }
		},
		// New function to get a single contactmoment
		async getContactMoment(id) {
			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new ContactMoment(data)

			this.setContactMomentItem(data)

			return { response, data, entity }
		},
		// Delete a contactmoment
		async deleteContactMoment(contactMomentItem) {
			if (!contactMomentItem.id) {
				throw new Error('No contactmoment item to delete')
			}

			const endpoint = `${apiEndpoint}/${contactMomentItem.id}`

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshContactMomentenList()

			return { response }
		},
		// Create or save a contactmoment from store
		async saveContactMoment(contactMomentItem) {
			if (!contactMomentItem) {
				throw new Error('No contactmoment item to save')
			}

			const isNewContactMoment = !contactMomentItem.id
			const endpoint = isNewContactMoment
				? `${apiEndpoint}`
				: `${apiEndpoint}/${contactMomentItem.id}`
			const method = isNewContactMoment ? 'POST' : 'PUT'

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(contactMomentItem),
				},
			)

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new ContactMoment(data)

			this.setContactMomentItem(data)
			this.refreshContactMomentenList()

			return { response, data, entity }
		},
	},
})
