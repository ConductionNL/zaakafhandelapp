import { defineStore } from 'pinia'
import { TBesluit, Besluit } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/objects/besluiten'

type TOptions = {
	/**
	 * Save the besluit item to the store in 'besluitItem'
	 */
	setItem?: boolean
}

export const useBesluitStore = defineStore('besluiten', {
	state: () => ({
		besluitItem: null,
		zaakId: null,
	}),
	actions: {
		setBesluitItem(besluitItem: Besluit | TBesluit) {
			this.besluitItem = besluitItem && new Besluit(besluitItem)
			console.info('Active besluit item set to ' + besluitItem)
		},
		/**
		 * Fetch a single besluit item by its ID.
		 *
		 * @param zaakId - The ID of the zaak the besluit belongs to
		 * @param id - The ID of the besluit item to fetch.
		 * @param options - Options for fetching the besluit item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TBesluit, entity: Besluit }> } The response, raw data, and entity.
		 */
		async getBesluit(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TBesluit, entity: Besluit }> {
			if (!id) {
				throw new Error('No besluit item to fetch')
			}

			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching besluit item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Besluit(data)

			options.setItem && this.setBesluitItem(data)

			return { response, data, entity }
		},
		/**
		 * Fetch all besluiten.
		 *
		 * @param zaakId - Optional ID of the zaak to filter besluiten by
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TBesluit[], entities: Besluit[] }> } The response, raw data array, and entity array.
		 */
		async getBesluiten(zaakId: string = null): Promise<{ response: Response, data: TBesluit[], entities: Besluit[] }> {
			const params = new URLSearchParams()
			if (zaakId) {
				params.append('zaak', zaakId)
			}

			const queryString = params.toString()
			const endpoint = queryString
				? `${apiEndpoint}?${queryString}`
				: apiEndpoint

			console.info('Fetching besluiten')

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TBesluit[]
			const entities = data.map((item: TBesluit) => new Besluit(item))

			return { response, data, entities }
		},
		/**
		 * Delete a besluit item from the store.
		 *
		 * @param besluitId - The ID of the besluit item to delete.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteBesluit(besluitId: string): Promise<{ response: Response }> {
			if (!besluitId) {
				throw new Error('No besluit item to delete')
			}

			const endpoint = `${apiEndpoint}/${besluitId}`

			console.info('Deleting besluit item with id: ' + besluitId)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			return { response }
		},
		/**
		 * Save a besluit item to the store. If the besluit item does not have a uuid, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param besluitItem - The besluit item to save.
		 * @param options - Options for saving the besluit item. (default: `{ setItem: true }`)
		 * @throws If there is no besluit item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TBesluit, entity: Besluit }>} The response, raw data, and entity.
		 */
		async saveBesluit(
			besluitItem: Besluit | TBesluit,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TBesluit, entity: Besluit }> {
			if (!besluitItem) {
				throw new Error('No besluit item to save')
			}

			const isNewBesluit = !besluitItem?.id
			const endpoint = isNewBesluit
				? apiEndpoint
				: `${apiEndpoint}/${besluitItem?.id}`
			const method = isNewBesluit ? 'POST' : 'PUT'

			console.info('Saving besluit item with id: ' + besluitItem?.id)

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(besluitItem),
				},
			)

			if (!response.ok) {
				console.error(response)
				throw new Error(response.statusText || 'Failed to save besluit')
			}

			const data = await response.json() as TBesluit
			const entity = new Besluit(data)

			options.setItem && this.setBesluitItem(data)

			return { response, data, entity }
		},
	},
})
