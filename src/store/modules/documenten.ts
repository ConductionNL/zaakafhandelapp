import { defineStore } from 'pinia'
import { TDocument, Document } from '../../entities/index.js'

const apiEndpoint = '/index.php/apps/zaakafhandelapp/api/objects/documenten'

type TOptions = {
	/**
	 * Save the document item to the store in 'documentItem'
	 */
	setItem?: boolean
}

export const useDocumentStore = defineStore('documenten', {
	state: () => ({
		documentItem: null,
		documentenList: [],
		/**
		 * Set the zaakId, used when creating a new document on a zaak.
		 */
		zaakId: null,
	}),
	actions: {
		setDocumentItem(documentItem: Document | TDocument) {
			this.documentItem = documentItem && new Document(documentItem)
			console.info('Active document item set to ' + documentItem)
		},
		setDocumentenList(documentenList: Document[] | TDocument[]) {
			this.documentenList = documentenList.map(
			    (documentItem) => new Document(documentItem),
			)
			console.info('Documenten list set to ' + documentenList.length + ' items')
		},
		/**
		 * Refresh the list of documenten items.
		 *
		 * @param search - Optional search query to filter the documenten list. (default: `null`)
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TDocument[], entities: Document[] }> } The response, raw data, and entities.
		 */
		async refreshDocumentenList(search: string = null): Promise<{ response: Response, data: TDocument[], entities: Document[] }> {
			let endpoint = apiEndpoint

			if (search !== null && search !== '') {
				endpoint = endpoint + '?_search=' + search
			}

			console.info('Refreshing documenten list with search: ' + search)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TDocument[]
			const entities = data.map((documentItem: TDocument) => new Document(documentItem))

			this.setDocumentenList(data)

			return { response, data, entities }
		},
		/**
		 * Fetch a single document item by its ID.
		 *
		 * @param id - The ID of the document item to fetch.
		 * @param options - Options for fetching the document item. (default: `{}`)
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TDocument, entity: Document }> } The response, raw data, and entity.
		 */
		async getDocument(
			id: string,
			options: TOptions = {},
		): Promise<{ response: Response, data: TDocument, entity: Document }> {
			const endpoint = `${apiEndpoint}/${id}`

			console.info('Fetching document item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = await response.json()
			const entity = new Document(data)

			options.setItem && this.setDocumentItem(data)

			return { response, data, entity }
		},
		/**
		 * Fetch all documenten.
		 *
		 * @param zaakId - Optional ID of the zaak to filter documenten by
		 * @throws If the HTTP request fails.
		 * @return { Promise<{ response: Response, data: TDocument[], entities: Document[] }> } The response, raw data array, and entity array.
		 */
		async getDocumenten(zaakId: string = null): Promise<{ response: Response, data: TDocument[], entities: Document[] }> {
			const params = new URLSearchParams()
			if (zaakId) {
				params.append('zaak', zaakId)
			}

			const queryString = params.toString()
			const endpoint = queryString
				? `${apiEndpoint}?${queryString}`
				: apiEndpoint

			console.info('Fetching documenten')

			const response = await fetch(endpoint, {
				method: 'GET',
			})

			if (!response.ok) {
				console.error(response)
				throw new Error(`HTTP error! status: ${response.status}`)
			}

			const data = (await response.json()).results as TDocument[]
			const entities = data.map((item: TDocument) => new Document(item))

			return { response, data, entities }
		},
		/**
		 * Delete a document item from the store.
		 *
		 * @param id - The ID of the document item to delete.
		 * @throws If the HTTP request fails.
		 * @return {Promise<{ response: Response }>} The response from the delete request.
		 */
		async deleteDocument(id: string): Promise<{ response: Response }> {
			if (!id) {
				throw new Error('No id for document item to delete')
			}

			const endpoint = `${apiEndpoint}/${id}`

			console.info('Deleting document item with id: ' + id)

			const response = await fetch(endpoint, {
				method: 'DELETE',
			})

			this.refreshDocumentenList()

			return { response }
		},
		/**
		 * Save a document item to the store. If the document item does not have a uuid, it will be created.
		 * Otherwise, it will be updated.
		 *
		 * @param documentItem - The document item to save.
		 * @param options - Options for saving the document item. (default: `{ setItem: true }`)
		 * @throws If there is no document item to save or if the HTTP request fails.
		 * @return {Promise<{ response: Response, data: TDocument, entity: Document }>} The response, raw data, and entity.
		 */
		async saveDocument(
			documentItem: Document | TDocument,
			options: TOptions = { setItem: true },
		): Promise<{ response: Response, data: TDocument, entity: Document }> {
			if (!documentItem) {
				throw new Error('No document item to save')
			}

			const isNewDocument = !documentItem?.id
			const endpoint = isNewDocument
				? `${apiEndpoint}`
				: `${apiEndpoint}/${documentItem?.id}`
			const method = isNewDocument ? 'POST' : 'PUT'

			console.info('Saving document item with id: ' + documentItem?.id)

			const response = await fetch(
				endpoint,
				{
					method,
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(documentItem),
				},
			)

			if (!response.ok) {
				console.error(response)
				throw new Error(response.statusText || 'Failed to save document')
			}

			const data = await response.json() as TDocument
			const entity = new Document(data)

			options.setItem && this.setDocumentItem(data)
			this.refreshDocumentenList()

			return { response, data, entity }
		},
	},
})
