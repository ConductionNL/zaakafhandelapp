import { defineStore } from 'pinia'
import { TZaakType, ZaakType } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen'

type TOptions = {
	/**
	 * Save the zaaktype item to the store in 'zaakTypeItem'
	 */
	setItem?: boolean
}

export const useZaakTypeStore = defineStore('zaakTypen', {
	state: () => ({
		zaakTypeItem: null,
		zaakTypeList: [],
	}),
	actions: {
		setZaakTypeItem(zaakTypeItem: ZaakType | TZaakType) {
			this.zaakTypeItem = zaakTypeItem && new ZaakType(zaakTypeItem)
			console.info('Active zaaktype item set to ' + zaakTypeItem)
		},
		setZaakTypeList(zaakTypeList: ZaakType[] | TZaakType[]) {
			this.zaakTypeList = zaakTypeList.map(
			    (zaakTypeItem) => new ZaakType(zaakTypeItem),
			)
			console.info('Zaaktypen list set to ' + zaakTypeList.length + ' items')
		},
		/**
		 * Refresh the list of zaaktype items.
		 *
		 * @param search - Optional search query to filter the zaaktypen list. (default: `null`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaakType[], entities: ZaakType[] }>} The response, raw data, and entities.
		 */
		async refreshZaakTypeList(search: string = null): Promise<{ response: Response, data: TZaakType[], entities: ZaakType[] }> {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			console.info('Refreshing zaaktypen list with search: ' + search)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TZaakType[]
			const entities = data.map((zaakTypeItem: TZaakType) => new ZaakType(zaakTypeItem))

			this.setZaakTypeList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single zaaktype item by its ID.
		 *
		 * @param id - The ID of the zaaktype item to fetch.
		 * @param options - Options for fetching the zaaktype item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaakType, entity: ZaakType }>} The response, raw data, and entity.
		 */
		async getZaakType(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TZaakType, entity: ZaakType }> {
			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching zaaktype item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new ZaakType(data)

			options.setItem && this.setZaakTypeItem(data)

			return { response, data, entity }
		},
		/**
		 * Delete a zaaktype item from the store.
		 *
		 * @param zaakTypeItem - The zaaktype item to delete.
		 * @throws If there is no zaaktype item to delete or if the zaaktype item does not have an id.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteZaakType(zaakTypeItem: ZaakType | TZaakType): Promise<{ response: Response }> {
			if (!zaakTypeItem) {
				throw new Error('No zaaktype item to delete')
			}
			if (!zaakTypeItem.id) {
				throw new Error('No id for zaaktype item to delete')
			}

			const endpoint = `${apiEndpoint}/${zaakTypeItem.id}`

			console.info('Deleting zaaktype item with id: ' + zaakTypeItem.id)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshZaakTypeList()

			return { response }
		},
		/**
		 * Save a zaaktype item to the store. If the zaaktype item does not have an id, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param zaakTypeItem - The zaaktype item to save.
		 * @param options - Options for saving the zaaktype item. (default: `{ setItem: true }`)
		 * @throws If there is no zaaktype item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TZaakType, entity: ZaakType }>} The response, raw data, and entity.
		 */
		async saveZaakType(
			zaakTypeItem: ZaakType | TZaakType,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TZaakType, entity: ZaakType }> {
			if (!zaakTypeItem) {
				throw new Error('No zaaktype item to save')
			}

			const isNewZaakType = !zaakTypeItem.id
			const endpoint = isNewZaakType
				? `${apiEndpoint}`
				: `${apiEndpoint}/${zaakTypeItem.id}`
			const method = isNewZaakType ? 'POST' : 'PUT'

			console.info('Saving zaaktype item with id: ' + zaakTypeItem.id)

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
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json() as TZaakType
			const entity = new ZaakType(data)

			options.setItem && this.setZaakTypeItem(data)
			this.refreshZaakTypeList()

			return { response, data, entity }
		},
	},
})
