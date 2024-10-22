import { defineStore } from 'pinia'
import { TZaak, Zaak } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/zrc/zaken'

type TOptions = {
	/**
	 * Save the zaak item to the store in 'zaakItem'
	 */
	setItem?: boolean
}

export const useZaakStore = defineStore('zaken', {
	state: () => ({
		zaakItem: null,
		zakenList: [],
	}),
	actions: {
		setZaakItem(zaakItem: Zaak | TZaak) {
			this.zaakItem = zaakItem && new Zaak(zaakItem)
			console.info('Active zaak item set to ' + zaakItem)
		},
		setZakenList(zakenList: Zaak[] | TZaak[]) {
			this.zakenList = zakenList.map(
			    (zaakItem) => new Zaak(zaakItem),
			)
			console.info('Zaken list set to ' + zakenList.length + ' items')
		},
		/**
		 * Refresh the list of zaken items.
		 *
		 * @param search - Optional search query to filter the zaken list. (default: `null`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaak[], entities: Zaak[] }>} The response, raw data, and entities.
		 */
		async refreshZakenList(search: string = null): Promise<{ response: Response, data: TZaak[], entities: Zaak[] }> {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			console.info('Refreshing zaken list with search: ' + search)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TZaak[]
			const entities = data.map((zaakItem: TZaak) => new Zaak(zaakItem))

			this.setZakenList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single zaak item by its ID.
		 *
		 * @param id - The ID of the zaak item to fetch.
		 * @param options - Options for fetching the zaak item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaak, entity: Zaak }>} The response, raw data, and entity.
		 */
		async getZaak(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TZaak, entity: Zaak }> {
			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching zaak item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Zaak(data)

			options.setItem && this.setZaakItem(data)

			return { response, data, entity }
		},
		/**
		 * Delete a zaak item from the store.
		 *
		 * @param zaakItem - The zaak item to delete.
		 * @throws If there is no zaak item to delete or if the zaak item does not have a uuid.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteZaak(zaakItem: Zaak | TZaak): Promise<{ response: Response }> {
			if (!zaakItem) {
				throw new Error('No zaak item to delete')
			}
			if (!zaakItem.uuid) {
				throw new Error('No uuid for zaak item to delete')
			}

			const endpoint = `${apiEndpoint}/${zaakItem.uuid}`

			console.info('Deleting zaak item with id: ' + zaakItem.uuid)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshZakenList()

			return { response }
		},
		/**
		 * Save a zaak item to the store. If the zaak item does not have a uuid, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param zaakItem - The zaak item to save.
		 * @param options - Options for saving the zaak item. (default: `{ setZaakItem: true }`)
		 * @throws If there is no zaak item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaak, entity: Zaak }>} The response, raw data, and entity.
		 */
		async saveZaak(
			zaakItem: Zaak | TZaak,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TZaak, entity: Zaak }> {
			if (!zaakItem) {
				throw new Error('No zaak item to save')
			}

			const isNewZaak = !zaakItem.uuid
			const endpoint = isNewZaak
				? `${apiEndpoint}`
				: `${apiEndpoint}/${zaakItem.uuid}`
			const method = isNewZaak ? 'POST' : 'PUT'

			console.info('Saving zaak item with id: ' + zaakItem.uuid)

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
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json() as TZaak
			const entity = new Zaak(data)

			options.setItem && this.setZaakItem(data)
			this.refreshZakenList()

			return { response, data, entity }
		},
	},
})
