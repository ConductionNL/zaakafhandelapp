import { defineStore } from 'pinia'
import { TResultaat, Resultaat } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/objects/resultaten'

type TOptions = {
	/**
	 * Save the resultaat item to the store in 'resultaatItem'
	 */
	setItem?: boolean
}

export const useResultaatStore = defineStore('resultaten', {
	state: () => ({
		resultaatItem: null,
		resultatenList: [],
		/**
		 * Set the zaakId, used when creating a new resultaat on a zaak.
		 */
		zaakId: null,
	}),
	actions: {
		setResultaatItem(resultaatItem: Resultaat | TResultaat) {
			this.resultaatItem = resultaatItem && new Resultaat(resultaatItem)
			console.info('Active resultaat item set to ' + resultaatItem)
		},
		setResultatenList(resultatenList: Resultaat[] | TResultaat[]) {
			this.resultatenList = resultatenList.map(
			    (resultaatItem) => new Resultaat(resultaatItem),
			)
			console.info('Resultaten list set to ' + resultatenList.length + ' items')
		},
		/**
		 * Refresh the list of resultaten items.
		 *
		 * @param search - Optional search query to filter the resultaten list. (default: `null`)
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TResultaat[], entities: Resultaat[] }> } The response, raw data, and entities.
		 */
		async refreshResultatenList(search: string = null): Promise<{ response: Response, data: TResultaat[], entities: Resultaat[] }> {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			console.info('Refreshing resultaten list with search: ' + search)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TResultaat[]
			const entities = data.map((resultaatItem: TResultaat) => new Resultaat(resultaatItem))

			this.setResultatenList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single resultaat item by its ID.
		 *
		 * @param id - The ID of the resultaat item to fetch.
		 * @param options - Options for fetching the resultaat item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TResultaat, entity: Resultaat }> } The response, raw data, and entity.
		 */
		async getResultaat(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TResultaat, entity: Resultaat }> {
			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching resultaat item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Resultaat(data)

			options.setItem && this.setResultaatItem(data)

			return { response, data, entity }
		},
		/**
		 * Delete a resultaat item from the store.
		 *
		 * @param resultaatItem - The resultaat item to delete.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteResultaat(resultaatItem: Resultaat | TResultaat): Promise<{ response: Response }> {
			if (!resultaatItem) {
				throw new Error('No resultaat item to delete')
			}
			if (!resultaatItem.id) {
				throw new Error('No id for resultaat item to delete')
			}

			const endpoint = `${apiEndpoint}/${resultaatItem.id}`

			console.info('Deleting resultaat item with id: ' + resultaatItem.id)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			this.refreshResultatenList()

			return { response }
		},
		/**
		 * Save a resultaat item to the store. If the resultaat item does not have a uuid, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param resultaatItem - The resultaat item to save.
		 * @param options - Options for saving the resultaat item. (default: `{ setItem: true }`)
		 * @throws If there is no resultaat item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TResultaat, entity: Resultaat }>} The response, raw data, and entity.
		 */
		async saveResultaat(
			resultaatItem: Resultaat | TResultaat,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TResultaat, entity: Resultaat }> {
			if (!resultaatItem) {
				throw new Error('No resultaat item to save')
			}

			const isNewResultaat = !resultaatItem?.id
			const endpoint = isNewResultaat
				? `${apiEndpoint}`
				: `${apiEndpoint}/${resultaatItem?.id}`
			const method = isNewResultaat ? 'POST' : 'PUT'

			console.info('Saving resultaat item with id: ' + resultaatItem?.id)

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(resultaatItem),
				},
			)

			if (!response.ok) {
				console.error(response)
				throw new Error(response.statusText || 'Failed to save resultaat')
			}

			const data = await response.json() as TResultaat
			const entity = new Resultaat(data)

			options.setItem && this.setResultaatItem(data)
			this.refreshResultatenList()

			return { response, data, entity }
		},
	},
})
