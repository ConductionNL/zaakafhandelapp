import { defineStore } from 'pinia'
import { TRol, Rol } from '../../entities/index.js'
import router from '../../router/router'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/objects/rollen'

type TOptions = {
	/**
	 * Save the rol item to the store in 'rolItem'
	 */
	setItem?: boolean
	/**
	 * Redirect to the rol details page after saving the rol item
	 */
	redirect?: boolean
}

export const useRolStore = defineStore('rollen', {
	state: () => ({
		rolItem: null as Rol,
		rollenList: [] as Rol[],
		zaakId: null as string,
		zaakUrl: null as string,
		/**
		 * Extra data to pass through the application.
		 *
		 * This is used for data which is required to be used somewhere else, but there is no state for, or cannot be inferred from other data.
		 *
		 * It is vital to clean this data up after usage.
		 *
		 * @example
		 * - redirect: true
		 * - redirect: false
		 * - anything: 'something'
		 */
		extraData: {} as Record<string, any>,
	}),
	actions: {
		setRolItem(rolItem: Rol | TRol) {
			this.rolItem = rolItem && new Rol(rolItem)
			console.info('Active rol item set to ' + rolItem)
		},
		setRollenList(rollenList: Rol[] | TRol[]) {
			this.rollenList = rollenList.map(
			    (rolItem) => new Rol(rolItem),
			)
			console.info('Rollen list set to ' + rollenList.length + ' items')
		},
		setZaakId(zaakId: string) {
			this.zaakId = zaakId
		},
		/**
		 * Refresh the list of rollen items.
		 *
		 * @param search - Optional search query to filter the rollen list. (default: `null`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TRol[], entities: Rol[] }>} The response, raw data, and entities.
		 */
		async refreshRollenList(search: string = null): Promise<{ response: Response, data: TRol[], entities: Rol[] }> {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			console.info('Refreshing rollen list with search: ' + search)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TRol[]
			const entities = data.map((rolItem: TRol) => new Rol(rolItem))

			this.setRollenList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single rol item by its ID.
		 *
		 * @param id - The ID of the rol item to fetch.
		 * @param options - Options for fetching the rol item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TRol, entity: Rol }>} The response, raw data, and entity.
		 */
		async getRol(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TRol, entity: Rol }> {
			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching rol item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Rol(data)

			options.setItem && this.setRolItem(data)

			return { response, data, entity }
		},
		/**
		 * Delete a rol item from the database.
		 *
		 * @param rolId - The id of the rol item to delete.
		 * @throws If there is no rol item to delete or if the rol item does not have an id.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteRol(rolId: string): Promise<{ response: Response }> {
			if (!rolId) {
				throw new Error('No rol item to delete')
			}

			const endpoint = `${apiEndpoint}/${rolId}`

			console.info('Deleting rol item with id: ' + rolId)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			this.refreshRollenList()
			router.push({ name: 'dynamic-view', params: { view: 'rollen' } })

			return { response }
		},
		/**
		 * Save a rol item to the database. If the rol item does not have an id, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param rolItem - The rol item to save.
		 * @param options - Options for saving the rol item. (default: `{ setItem: true }`)
		 * @throws If there is no rol item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TRol, entity: Rol }>} The response, raw data, and entity.
		 */
		async saveRol(
			rolItem: Rol | TRol,
			options: TOptions = { setItem: true, redirect: true },
		): Promise<{ response: Response, data: TRol, entity: Rol }> {
			if (!rolItem) {
				throw new Error('No rol item to save')
			}

			// Ensure options fall back to default values if not provided
			options = { setItem: true, redirect: true, ...options }

			const isNewRol = !rolItem.id
			const endpoint = isNewRol
				? `${apiEndpoint}`
				: `${apiEndpoint}/${rolItem.id}`
			const method = isNewRol ? 'POST' : 'PUT'

			console.info('Saving rol item with id: ' + rolItem.id)

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
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json() as TRol
			const entity = new Rol(data)

			options.setItem && this.setRolItem(data)
			this.refreshRollenList()
			if (options.redirect) router.push({ name: 'dynamic-view', params: { view: 'rollen', id: entity.id } })

			return { response, data, entity }
		},
	},
})
