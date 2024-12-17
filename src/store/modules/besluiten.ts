import { defineStore } from 'pinia'
import { TBesluit, Besluit } from '../../entities/index.js'

const getApiEndpoint = (zaakId: string) => `/index.php/apps/zaakafhandelapp/api/zrc/zaken/${zaakId}/besluiten`

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
			zaakId: string,
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TBesluit, entity: Besluit }> {
			const endpoint = `${getApiEndpoint(zaakId)}/${id}`

			console.info('Fetching besluit item with id: ' + id + ' from zaak: ' + zaakId)

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
		 * Fetch all besluiten for a zaak.
		 *
		 * @param zaakId - The ID of the zaak to fetch besluiten for
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TBesluit[], entities: Besluit[] }> } The response, raw data array, and entity array.
		 */
		async getBesluiten(
			zaakId: string,
		): Promise<{ response: Response, data: TBesluit[], entities: Besluit[] }> {
			const endpoint = getApiEndpoint(zaakId)

			console.info('Fetching besluiten for zaak: ' + zaakId)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const _data = (await response.json()).results as { [key: string]: TBesluit }

			/*
            As of now (17/12/2024) the data gets send back in this format
            {
                "results": {
                    "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f": {
                        "id": "5137a1e5-b54d-43ad-abd1-4b5bff5fcd3f",
                        "name": "Zaakt type 1",
                        "summary": "summary for one"
                    },
                }
            }
            Instead of the expected array of objects.
            So some processing is needed to get the data in the expected format.
            */

			const data = Object.values(_data)
			const entities = data.map((item: TBesluit) => new Besluit(item))

			return { response, data, entities }
		},
		/**
		 * Delete a besluit item from the store.
		 *
		 * @param zaakId - The ID of the zaak the besluit belongs to
		 * @param besluitId - The ID of the besluit item to delete.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteBesluit(zaakId: string, besluitId: string): Promise<{ response: Response }> {
			if (!besluitId) {
				throw new Error('No besluit item to delete')
			}

			const endpoint = `${getApiEndpoint(zaakId)}/${besluitId}`

			console.info('Deleting besluit item with id: ' + besluitId + ' from zaak: ' + zaakId)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			return { response }
		},
		/**
		 * Save a besluit item to the store. If the besluit item does not have a uuid, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param zaakId - The ID of the zaak the besluit belongs to
		 * @param besluitItem - The besluit item to save.
		 * @param options - Options for saving the besluit item. (default: `{ setItem: true }`)
		 * @throws If there is no besluit item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TBesluit, entity: Besluit }>} The response, raw data, and entity.
		 */
		async saveBesluit(
			zaakId: string,
			besluitItem: Besluit | TBesluit,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TBesluit, entity: Besluit }> {
			if (!besluitItem) {
				throw new Error('No besluit item to save')
			}

			const isNewBesluit = !besluitItem?.id
			const endpoint = isNewBesluit
				? getApiEndpoint(zaakId)
				: `${getApiEndpoint(zaakId)}/${besluitItem?.id}`
			const method = isNewBesluit ? 'POST' : 'PUT'

			console.info('Saving besluit item with id: ' + besluitItem?.id + ' to zaak: ' + zaakId)

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
