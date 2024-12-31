/* eslint-disable no-console */
import { defineStore } from 'pinia'
import { ContactMoment, TContactMoment } from '../../entities/index.js'
import router from '../../router/router'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/contactmomenten'

export const useContactMomentStore = defineStore('contactmomenten', {
	state: () => ({
		contactMomentItem: null as ContactMoment,
		contactMomentenList: [] as ContactMoment[],
	}),
	actions: {
		/**
		 * Set the active contact moment item.
		 *
		 * @param contactMomentItem - The contact moment item to set.
		 */
		setContactMomentItem(contactMomentItem: TContactMoment | ContactMoment) {
			this.contactMomentItem = contactMomentItem && new ContactMoment(contactMomentItem)
			console.info('Active contactmoment item set to ' + contactMomentItem)
		},
		/**
		 * Set the list of contact moments.
		 *
		 * @param contactMomentenList - The list of contact moments to set.
		 */
		setContactMomentenList(contactMomentenList: TContactMoment[] | ContactMoment[]) {
			this.contactMomentenList = contactMomentenList.map(
				(contactMomentItem) => new ContactMoment(contactMomentItem),
			)
			console.info('Contactmomenten list set to ' + contactMomentenList.length + ' items')
		},
		/**
		 * Refresh the list of contact moments.
		 *
		 * @param search - Optional search query to filter the contact moments list. (default: `null`)
		 * @param notClosed - Optional boolean to filter out closed contact moments from the contact moments list. (default: `false`)
		 * @param user
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TContactMoment[], entities: ContactMoment[] }>} The response, raw data, and entities.
		 */
		async refreshContactMomentenList(search: string = null, notClosed: boolean = false, user: string = null): Promise<{ response: Response, data: TContactMoment[], entities: ContactMoment[] }> {
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
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TContactMoment[]
			const entities = data.map((contactMomentItem) => new ContactMoment(contactMomentItem))

			this.setContactMomentenList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single contact moment by its ID.
		 *
		 * @param id - The ID of the contact moment to fetch.
		 * @throws If the ID is invalid or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TContactMoment, entity: ContactMoment }>} The response, raw data, and entity.
		 */
		async getContactMoment(id: string | number): Promise<{ response: Response, data: TContactMoment, entity: ContactMoment }> {
			if (!id || (typeof id !== 'string' && typeof id !== 'number') || (typeof id === 'string' && id.trim() === '')) {
				throw new Error('Invalid ID provided for fetching contact moment')
			}

			const endpoint = `${apiEndpoint}/${id}`

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json() as TContactMoment
			const entity = new ContactMoment(data)

			this.setContactMomentItem(data)

			return { response, data, entity }
		},
		/**
		 * Delete a contact moment.
		 *
		 * @param contactMomentItem - The contact moment item to delete.
		 * @throws If there is no contact moment item to delete or if the contact moment item does not have an id.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteContactMoment(contactMomentItem: ContactMoment): Promise<{ response: Response }> {
			if (!contactMomentItem || !contactMomentItem.id) {
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
			router.push({ name: 'dynamic-view', params: { view: 'contactmomenten' } })

			return { response }
		},
		/**
		 * Save a contact moment to the database. If the contact moment does not have an id, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param contactMomentItem - The contact moment item to save.
		 * @throws If there is no contact moment item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TContactMoment, entity: ContactMoment }>} The response, raw data, and entity.
		 */
		async saveContactMoment(contactMomentItem: TContactMoment | ContactMoment): Promise<{ response: Response, data: TContactMoment, entity: ContactMoment }> {
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
					body: JSON.stringify({ ...contactMomentItem }),
				},
			)

			if (!response.ok) {
				console.info(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json() as TContactMoment
			const entity = new ContactMoment(data)

			this.setContactMomentItem(data)
			this.refreshContactMomentenList()
			router.push({ name: 'dynamic-view', params: { view: 'contactmomenten', id: entity.id } })

			return { response, data, entity }
		},
	},
})
