import { defineStore } from 'pinia'

/**
 * @description
 * Errors are thrown up the function tree until they are caught by a function that can actually use it.
 *
 * Error handling mental model:
 * - Ask "Can I do something useful with this error right here?"
 * - - Yes → handle it locally (retry, default value, user prompt, cleanup…).
 * - - No → enrich it with context and re‑throw so that a higher layer, one that can decide, gets it.
 * - Log once, at the boundary that finally decides.
 * - - Duplicated logging from every layer clutters output and makes alerting noisy.
 * - Add context without losing the original stack.
 * - - In modern Node (v16.9+) and in the browser (ECMAscript 2022) you can chain errors:
 * - - - `throw new DatabaseError("Could not write invoice", { cause: err });`
 * - - When it surfaces you still have the full chain:
 * - - - `log.error(topErr, "Payment flow failed");`
 * - Keep `try ... catch` as small as it needs to be, and no smaller.
 * - - Put a `try … catch` only around the statements for which you have a real reaction.
 *     Everything else should be allowed to bubble upward, where a higher‑level handler can deal with it.
 */
// eslint-disable-next-line no-unused-vars, camelcase, @typescript-eslint/no-unused-vars
const Readme_Error_handling = {}

type Type = string
type Id = string

// this type got created in the original code, but is not used anywhere.
// eslint-disable-next-line @typescript-eslint/no-unused-vars
type Schema = {
    id: number;
    uuid: string;
    slug: string;
    title: string;
    properties: object;
}

type Timer = ReturnType<typeof setTimeout>

type TSettings = {
    objectTypes: Array<string>;
    configuration: Record<string, string>;
    // THE FOLLOWING TYPES DO NOT EXIST ON THE ORIGINAL CODE YET WERE REFERENCED.
    // someone needs to go through this and add the correct types.
    availableRegisters: Record<string, any>[];
    availableSchemas: Record<string, any>[];
}

type TCollections = Record<Type, { results: Array<any> }>

type TRelatedDataTypes = {
    logs: Array<any>;
    uses: Record<string, string>;
    used: Record<string, string>;
    files: Record<string, string>;
}
const relatedDataTypesKeys: Array<keyof TRelatedDataTypes> = ['logs', 'uses', 'used', 'files']

type TPagination = {
    total: number;
    page: number;
    pages: number;
    limit: number;
    next: string | null;
    prev: string | null;
}

interface State {
    /** Application settings */
    settings: TSettings | null
    /** Objects by type and ID */
    objects: Record<Type, Record<Id, any>>
    /** Collections by type */
    collections: TCollections
    /** Loading states by type */
    loading: Record<Type, boolean>
    /** Error states by type */
    errors: Record<Type, string | null>
    /** Currently active objects by type */
    activeObjects: Record<Type, any>
    /** Related data for active objects */
    relatedData: Record<Type, TRelatedDataTypes>
    /** Search terms by collection type */
    searchTerms: Record<Type, string>
    /** Search debounce timers by collection type */
    searchDebounceTimers: Record<Type, Timer | null>
    /** Pagination state by type */
    pagination: Record<Type, TPagination>
    /** Success states by type */
    success: Record<Type, boolean | null>
}

/**
 * Store for managing all object types
 * @package
 * @author Ruben Linde <ruben@Conduction.nl>
 * @copyright 2024 Conduction
 * @license AGPL-3.0
 * @version 1.0.0
 */
export const useObjectStore = defineStore('object', {
	state: (): State => {
		return {
			settings: null,
			objects: {},
			collections: {},
			loading: {},
			errors: {},
			activeObjects: {},
			relatedData: {},
			searchTerms: {},
			searchDebounceTimers: {},
			pagination: {},
			success: {},
		}
	},

	getters: {
		/**
		 * Get object types from settings
		 * @param state the state of the store
		 */
		objectTypes(state: State) {
			return state.settings?.objectTypes || []
		},

		/**
		 * Get available registers from settings
		 * @param state the state of the store
		 */
		availableRegisters(state: State) {
			return state.settings?.availableRegisters || []
		},

		/**
		 * Get available schemas from settings
		 * @param state the state of the store
		 */
		availableSchemas(state: State) {
			if (!state.settings?.availableRegisters) return []
			return state.settings.availableRegisters.flatMap(register =>
				register.schemas.map((schema: Record<string, any>) => ({
					...schema,
					registerId: register.id,
					registerTitle: register.title,
				})),
			)
		},

		/**
		 * Get loading state for specific type.
		 *
		 * Returns a function that when called with a object type, returns the loading state for that object type.
		 * @param state the state of the store
		 */
		isLoading(state: State) {
			return (type: Type) => state.loading[type] || false
		},

		/**
		 * Get error for specific type.
		 *
		 * Returns a function that when called with a object type, returns the error for that object type.
		 * @param state the state of the store
		 */
		getError(state: State) {
			return (type: Type) => state.errors[type] || null
		},

		/**
		 * Get collection for specific type.
		 *
		 * Returns a function that when called with a object type, returns the collection for that object type.
		 * @param state the state of the store
		 */
		getCollection(state: State) {
			return (type: Type) => {
				console.info('getCollection called for type:', type, {
					collection: state.collections[type],
					collectionType: typeof state.collections[type],
					hasResults: state.collections[type]?.results?.length > 0,
				})

				return state.collections[type] || { results: [] } as TCollections[Type]
			}
		},

		/**
		 * Get search term for specific type.
		 *
		 * Returns a function that when called with a object type, returns the search term for that object type.
		 * @param state the state of the store
		 */
		getSearchTerm(state: State) {
			return (type: Type) => state.searchTerms[type] || ''
		},

		/**
		 * Get single object.
		 *
		 * Returns a function that when called with a object type and id, returns the single object for that object type and id.
		 * @param state the state of the store
		 */
		getObject(state: State) {
			return (type: Type, id: Id) => state.objects[type]?.[id] || null
		},

		/**
		 * Get active object for type.
		 *
		 * Returns a function that when called with a object type, returns the active object for that object type.
		 * @param state the state of the store
		 */
		getActiveObject(state: State) {
			return (type: Type) => state.activeObjects[type] || null
		},

		/**
		 * Get related data for active object
		 *
		 * Returns a function that when called with a object type and dataType, returns the related data for that object type and dataType.
		 * @param state the state of the store
		 */
		getRelatedData(state: State) {
			return (type: Type, dataType: keyof TRelatedDataTypes) => state.relatedData[type]?.[dataType] || null
		},

		/**
		 * Get pagination info for type
		 *
		 * Returns a function that when called with a object type, returns the pagination info for that object type.
		 * @param state the state of the store
		 */
		getPagination(state: State) {
			return (type: Type) => state.pagination[type] || { total: 0, page: 1, pages: 1, limit: 20, next: null, prev: null } as TPagination
		},

		/**
		 * Check if there are more pages to load for type
		 *
		 * Returns a function that when called with a object type, returns a boolean indicating if there are more pages to load for that object type.
		 * @param state the state of the store
		 */
		hasMorePages(state: State) {
			return (type: Type) => {
				const pagination = state.pagination[type]
				return pagination ? (pagination.next !== null || pagination.page < pagination.pages) : false
			}
		},

		/**
		 * Check if there are previous pages available
		 *
		 * Returns a function that when called with a object type, returns a boolean indicating if there are previous pages available for that object type.
		 * @param state the state of the store
		 */
		hasPreviousPages(state: State) {
			return (type: Type) => {
				const pagination = state.pagination[type]
				return pagination ? (pagination.prev !== null || pagination.page > 1) : false
			}
		},

		/**
		 * Get audit trails for type
		 *
		 * Returns a function that when called with a object type, returns the audit trails for that object type.
		 * @param state the state of the store
		 */
		getAuditTrails(state: State) {
			return (type: Type) => state.relatedData[type]?.logs || []
		},

		/**
		 * Get state for specific type
		 *
		 * Returns a function that when called with a object type, returns the state for that object type.
		 * @param state the state of the store
		 */
		getState(state: State) {
			return (type: Type) => ({
				success: state.success[type] || null,
				error: state.errors[type] || null,
			})
		},
	},

	actions: {
		/**
		 * Set collection for type
		 * @param type - Object type
		 * @param results - Collection results
		 * @param append - Whether to append results to existing collection
		 */
		setCollection(
			type: Type,
			results: State['collections'][Type]['results'],
			append: boolean = false,
		) {
			console.info('setCollection called with:', {
				type,
				resultsLength: results?.length,
				append,
				currentCollection: this.collections[type],
				currentCollectionType: typeof this.collections[type],
			})

			// Initialize if needed
			if (!this.collections[type] || !append) {
				console.info('Initializing collection for type:', type)
				this.collections[type] = { results: [] }
			}

			// Update the collection using reactive assignment
			const newResults = append
				? [...(this.collections[type].results || []), ...results]
				: results

			console.info('Setting new results:', {
				newResultsLength: newResults?.length,
				firstItem: newResults?.[0],
			})

			// Use reactive assignment for collections
			this.collections = {
				...this.collections,
				[type]: {
					results: newResults,
				},
			}

			console.info('Collection after update:', {
				type,
				collection: this.collections[type],
				length: this.collections[type]?.results?.length,
			})
		},

		/**
		 * Set loading state for type
		 * @param type - Object type (can be a composite id like `${type}_${id}`)
		 * @param isLoading - Loading state
		 */
		setLoading(type: Type, isLoading: boolean) {
			this.loading = {
				...this.loading,
				[type]: isLoading,
			}
			console.info('Loading state set:', { type, isLoading })
		},

		/**
		 * Set error for type
		 * @param type - Object type
		 * @param error - Error message
		 */
		setError(type: Type, error: State['errors'][Type]) {
			this.errors = {
				...this.errors,
				[type]: error,
			}
			if (error) {
				console.error('Error set for type:', type, error)
			}
		},

		/**
		 * Set active object for type and fetch related data
		 * @param type - Object type
		 * @param object - Object to set as active
		 */
		async setActiveObject(type: Type, object: State['objects'][Type][Id]) {
			console.info('setActiveObject called with:', { type, object })
			// Log the current state before update
			console.info('Current activeObjects state:', { ...this.activeObjects })
			// Update using reactive assignment
			this.activeObjects = {
				...this.activeObjects,
				[type]: object,
			} as State['activeObjects']
			// Log the state after update
			console.info('Updated activeObjects state:', { ...this.activeObjects })

			// Initialize related data structure if not exists
			console.info('Initializing relatedData for type:', type)
			this.relatedData = {
				...this.relatedData,
				[type]: {
					logs: null,
					uses: null,
					used: null,
					files: null,
				},
			} as State['relatedData']

			// Fetch related data in parallel
			if (object?.id) {
				console.info('Fetching related data for:', { type, objectId: object.id })
				const fetchPromises = []
				for (const dataType of relatedDataTypesKeys) {
					if (!this.relatedData[type][dataType]) {
						fetchPromises.push(this.fetchRelatedData(type, object.id, dataType))
					}
				}
				await Promise.all(fetchPromises)
				console.info('Finished fetching related data')
			} else {
				console.info('No object ID provided, skipping related data fetch')
			}
			console.info('setActiveObject completed')
		},

		/**
		 * Clear active object for type
		 * @param type - Object type
		 */
		clearActiveObject(type: Type) {
			delete this.activeObjects[type]
			delete this.relatedData[type]
		},

		/**
		 * Get schema configuration for object type
		 * @param type - Type of object
		 * @throws {Error} If settings not found or invalid configuration
		 */
		getSchemaConfig(type: Type) {
			if (!this.settings) {
				throw new Error('Settings not loaded')
			}

			const config = this.settings.configuration
			const source = config[`${type}_source`]
			const schema = config[`${type}_schema`]
			const register = config[`${type}_register`]

			if (!source || !schema || !register) {
				throw new Error(`Invalid configuration for object type: ${type}`)
			}

			return { source, schema, register }
		},

		/**
		 * Constructs the API endpoint URL for objects
		 * @param type - Object type
		 * @param id - Object ID (optional)
		 * @param action - Additional action (e.g., 'logs', 'uses') (optional)
		 * @param params - Query parameters (optional)
		 * @param params._limit - Number of results per page (optional)
		 * @param params._page - Page number (optional)
		 * @param params.extend - Extend (optional)
		 * @private
		 */
		_constructApiUrl(
			type: Type,
			id: Id | null = null,
			action: string | null = null,
			params: {
				_limit?: string | undefined;
				_page?: string | undefined;
				extend?: string | undefined;
				[key: string]: string | undefined;
			} = {},
		): string {
			const config = this.getSchemaConfig(type)
			const baseUrl = '/index.php/apps/openregister/api/objects'

			// Construct the path with register and schema
			let url = `${baseUrl}/${config.register}/${config.schema}`

			// Add ID and action if provided
			if (id) {
				url += `/${id}`
				if (action) {
					// Special case for audit trails
					if (action === 'logs') {
						url += '/audit-trails'
					} else {
						url += `/${action}`
					}
				}
			}

			// Add pagination and other query parameters
			const queryParams = new URLSearchParams({
				_limit: params._limit || '20',
				_page: params._page || '1',
				extend: params.extend || '@self.schema',
				...params,
			})

			// Remove source, schema, and register from query params as they're now in the URL
			queryParams.delete('_source')
			queryParams.delete('_schema')
			queryParams.delete('_register')

			return `${url}?${queryParams}`
		},

		/**
		 * Send a fetch request to the API.
		 *
		 * Throws an error if the request fails or the response is not OK.
		 * @param url - The URL to fetch
		 * @param method - The HTTP method to use
		 * @param headers - Additional headers to include in the request, defaults to `{ 'Content-Type': 'application/json' }`
		 * @param fetchParams - extra settings for the fetch request
		 * @private
		 */
		async _sendFetchRequest(
			url: string,
			method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH' | 'OPTIONS' = 'GET',
			fetchParams: Record<string, any> = {},
		) {
			let response: Response

			try {
				response = await fetch(url, {
					method,
					headers: { 'Content-Type': 'application/json' },
					...fetchParams,
				})
			} catch (error) {
				throw new Error(`Network request failed for ${url}`, { cause: error })
			}

			if (!response.ok) throw new Error(`HTTP ${response.status} while fetching ${url}`)

			return response
		},

		/**
		 * Fetch collection of objects
		 * @param type - Object type
		 * @param params - Query parameters
		 * @param append - Whether to append results to existing collection
		 */
		async fetchCollection(type: Type, params: Record<string, string> = {}, append: boolean = false) {
			try {
				console.info('fetchCollection started:', { type, params, append })
				this.setLoading(type, true)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				// Add extend parameter if not explicitly set
				const queryParams = {
					...params,
					extend: params.extend || '@self.schema',
				}

				const url = this._constructApiUrl(type, null, null, queryParams)
				const response = await this._sendFetchRequest(url)

				let data
				try {
					data = await response.json()
				} catch (error) {
					throw new Error(`Failed to parse ${type} collection response`, { cause: error })
				}

				console.info('API Response:', data)

				// Update pagination info - handle both pagination formats
				const paginationInfo = {
					total: data.total || 0,
					page: data.page || 1,
					pages: data.pages || (data.next ? Math.ceil((data.total || 0) / (data.limit || 20)) : 1),
					limit: data.limit || 20,
					next: data.next || null,
					prev: data.prev || null,
				} as TPagination

				this.setPagination(type, paginationInfo)

				// Set the collection using the new method
				this.setCollection(type, data.results, append)

				// Update objects cache with extended data
				if (!this.objects[type]) {
					this.objects[type] = {}
				}
				data.results.forEach((item: any) => {
					this.objects[type][item.id] = { ...item }
				})
			} catch (error) {
				console.error(`Error fetching ${type} collection:`, error)
				this.setState(type, { success: false, error: error instanceof Error ? error.message : 'Unknown error' })
				throw new Error('Failed to fetch collection', { cause: error })
			} finally {
				this.setLoading(type, false)
			}
		},

		/**
		 * Fetch single object
		 * @param type - Object type
		 * @param id - Object ID
		 * @param params - Query parameters
		 */
		async fetchObject(type: Type, id: Id, params: Record<string, string> = {}) {
			const loadingCompositeId = `${type}_${id}`

			try {
				this.setLoading(loadingCompositeId, true)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				// Add extend parameter if not explicitly set
				const queryParams = {
					...params,
					extend: params.extend || '@self.schema',
				}

				const url = this._constructApiUrl(type, id, null, queryParams)
				const response = await this._sendFetchRequest(url)

				let data
				try {
					data = await response.json()
				} catch (error) {
					throw new Error(`Failed to parse ${type} object response`, { cause: error })
				}

				if (!this.objects[type]) this.objects[type] = {}
				this.objects[type][id] = data

				// If this object is currently active, update it and its related data
				if (this.activeObjects[type]?.id === id) {
					await this.setActiveObject(type, data)
				}
			} catch (error) {
				console.error(`Error fetching ${type} object:`, error)
				this.setState(type, { success: false, error: error instanceof Error ? error.message : 'Unknown error' })
				throw new Error('Failed to fetch object', { cause: error })
			} finally {
				this.setLoading(loadingCompositeId, false)
			}
		},

		/**
		 * Fetch related data for object
		 * @param type - Object type
		 * @param id - Object ID
		 * @param dataType - Type of related data (logs, uses, used, files)
		 * @param params - Query parameters
		 */
		async fetchRelatedData(
			type: Type,
			id: Id,
			dataType: keyof TRelatedDataTypes,
			params: Record<string, string> = {},
		) {
			const compositeId = `${type}_${id}_${dataType}`

			try {
				this.setLoading(compositeId, true)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				// Add extend parameter for 'uses' and 'used' data types
				const queryParams = {
					...params,
					...(dataType === 'uses' || dataType === 'used' ? { extend: params.extend || '@self.schema' } : {}),
				}

				const url = this._constructApiUrl(type, id, dataType, queryParams)
				const response = await this._sendFetchRequest(url)

				let data
				try {
					data = await response.json()
				} catch (error) {
					throw new Error(`Failed to parse ${dataType} for ${type} response`, { cause: error })
				}

				if (!this.relatedData[type]) {
					this.relatedData[type] = {
						logs: [],
						uses: {},
						used: {},
						files: {},
					} as TRelatedDataTypes
				}

				// For audit trails, store the results array
				if (dataType === 'logs') {
					this.relatedData[type][dataType] = data.results || []
				} else {
					this.relatedData[type][dataType] = data
				}
			} catch (error) {
				console.error(`Error fetching ${dataType} for ${type}:`, error)
				this.setState(type, { success: false, error: error instanceof Error ? error.message : 'Unknown error' })
				throw new Error('Failed to fetch related data', { cause: error })
			} finally {
				this.setLoading(`${type}_${id}_${dataType}`, false)
			}
		},

		/**
		 * Fetch and update settings
		 */
		async fetchSettings() {
			try {
				const response = await this._sendFetchRequest('/index.php/apps/opencatalogi/api/settings')

				let data
				try {
					data = await response.json()
				} catch (error) {
					throw new Error('Failed to parse settings response', { cause: error })
				}

				this.settings = data
			} catch (error) {
				throw new Error('Failed to fetch settings', { cause: error })
			}
		},

		/**
		 * Create new object
		 * @param type - Object type
		 * @param data - Object data
		 */
		async createObject(type: Type, data: Record<string, any>) {
			try {
				this.setLoading(`${type}_create`, true)
				this.setError(`${type}_create`, null)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				const url = this._constructApiUrl(type)
				const response = await this._sendFetchRequest(url, 'POST', {
					body: JSON.stringify(data),
				})

				let newObject
				try {
					newObject = await response.json()
				} catch (error) {
					throw new Error(`Failed to parse ${type} object response`, { cause: error })
				}

				if (!this.objects[type]) this.objects[type] = {}
				this.objects[type][newObject.id] = newObject

				// Refresh the collection to ensure it's up to date
				await this.fetchCollection(type)

				// Set success state
				this.setState(type, { success: true, error: null })

				return newObject
			} catch (error) {
				console.error(`Error creating ${type} object:`, error)
				const errorMessage = error instanceof Error ? error.message : 'Unknown error'
				this.setError(`${type}_create`, errorMessage)
				this.setState(type, { success: false, error: errorMessage })
				throw new Error('Failed to create object', { cause: error })
			} finally {
				this.setLoading(`${type}_create`, false)
			}
		},

		/**
		 * Update existing object
		 * @param type - Object type
		 * @param id - Object ID
		 * @param data - Updated object data
		 */
		async updateObject(type: Type, id: Id, data: Record<string, any>) {
			try {
				this.setLoading(`${type}_${id}`, true)
				this.setError(`${type}_${id}`, null)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				const url = this._constructApiUrl(type, id)
				const response = await this._sendFetchRequest(url, 'PUT', {
					body: JSON.stringify(data),
				})

				let updatedObject
				try {
					updatedObject = await response.json()
				} catch (error) {
					throw new Error(`Failed to parse ${type} object response`, { cause: error })
				}

				if (!this.objects[type]) this.objects[type] = {}
				this.objects[type][id] = updatedObject

				// Refresh the collection to ensure it's up to date
				await this.fetchCollection(type)

				// If this is the active object, update it
				if (this.activeObjects[type]?.id === id) {
					this.activeObjects[type] = updatedObject
				}

				// Set success state
				this.setState(type, { success: true, error: null })

				return updatedObject
			} catch (error) {
				console.error(`Error updating ${type} object:`, error)
				const errorMessage = error instanceof Error ? error.message : 'Unknown error'
				this.setError(`${type}_${id}`, errorMessage)
				this.setState(type, { success: false, error: errorMessage })
				throw new Error('Failed to update object', { cause: error })
			} finally {
				this.setLoading(`${type}_${id}`, false)
			}
		},

		/**
		 * Delete object
		 * @param type - Object type
		 * @param id - Object ID
		 */
		async deleteObject(type: Type, id: Id) {
			this.setLoading(`${type}_${id}`, true)
			this.setError(`${type}_${id}`, null)
			this.setState(type, { success: null, error: null })

			try {
				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				const url = this._constructApiUrl(type, id)
				await this._sendFetchRequest(url, 'DELETE')

				// Remove from objects
				if (this.objects[type]) {
					delete this.objects[type][id]
				}

				// If this was the active object, clear it
				if (this.activeObjects[type]?.id === id) {
					this.clearActiveObject(type)
				}

				// Refresh the collection to ensure it's up to date
				await this.fetchCollection(type)

				// Set success state
				this.setState(type, { success: true, error: null })
			} catch (error) {
				console.error(`Error deleting ${type} object:`, error)
				const errorMessage = error instanceof Error ? error.message : 'Unknown error'
				this.setError(`${type}_${id}`, errorMessage)
				this.setState(type, { success: false, error: errorMessage })
				throw new Error('Failed to delete object', { cause: error })
			} finally {
				this.setLoading(`${type}_${id}`, false)
			}
		},

		/**
		 * Set search term for type
		 * @param type - Object type
		 * @param term - Search term
		 */
		setSearchTerm(type: Type, term: string) {
			try {
				// Initialize search term if it doesn't exist
				if (!this.searchTerms[type]) {
					this.searchTerms = {
						...this.searchTerms,
						[type]: '',
					}
				}

				// Update search term with reactive assignment
				this.searchTerms = {
					...this.searchTerms,
					[type]: term,
				}

				// Clear existing debounce timer
				if (this.searchDebounceTimers[type]) {
					clearTimeout(this.searchDebounceTimers[type])
				}

				// Set new debounce timer
				this.searchDebounceTimers = {
					...this.searchDebounceTimers,
					[type]: setTimeout(() => {
						this.fetchCollection(type, term ? { _search: term } : {})
					}, 500),
				}
			} catch (error) {
				console.error(`Error setting search term for ${type}:`, error)
				throw new Error('Failed to set search term', { cause: error })
			}
		},

		/**
		 * Set pagination info for type
		 * @param type - Object type
		 * @param pagination - Pagination info
		 */
		setPagination(type: Type, pagination: TPagination) {
			this.pagination = {
				...this.pagination,
				[type]: pagination,
			}
		},

		/**
		 * Load next page of results
		 * @param type - Object type
		 */
		async loadMore(type: Type) {
			const pagination = this.getPagination(type)

			try {
				if (pagination.next) {
					// Extract query parameters from the next URL
					const url = new URL(pagination.next)
					const params = Object.fromEntries(url.searchParams)
					await this.fetchCollection(type, params, true)
				} else if (pagination.page < pagination.pages) {
					await this.fetchCollection(type, {
						_page: String(pagination.page + 1),
						_limit: String(pagination.limit),
					}, true)
				}
			} catch (error) {
				console.error(`Error loading more results for ${type}:`, error)
				throw new Error('Failed to load more results', { cause: error })
			}
		},

		/**
		 * Load previous page of results
		 * @param type - Object type
		 */
		async loadPrevious(type: Type) {
			const pagination = this.getPagination(type)

			try {
				if (pagination.prev) {
				// Extract query parameters from the prev URL
					const url = new URL(pagination.prev)
					const params = Object.fromEntries(url.searchParams)
					await this.fetchCollection(type, params, false)
				} else if (pagination.page > 1) {
					await this.fetchCollection(type, {
						_page: String(pagination.page - 1),
						_limit: String(pagination.limit),
					}, false)
				}
			} catch (error) {
				console.error(`Error loading previous page for ${type}:`, error)
				throw new Error('Failed to load previous page', { cause: error })
			}
		},

		/**
		 * Preload collections for all available schemas
		 * This function should be called once when the application initializes
		 */
		async preloadCollections() {
			try {
				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				// Get all available object types from settings
				const objectTypes = this.objectTypes

				console.info('Preloading collections for object types:', objectTypes)

				// Load collections for all object types in parallel
				await Promise.allSettled(
					objectTypes.map((type) => this.fetchCollection(type)),
				)

				console.info('Finished preloading collections')
			} catch (error) {
				console.error('Error preloading collections:', error)
				throw new Error('Failed to preload collections', { cause: error })
			}
		},

		/**
		 * Set state for specific type
		 * @param type - Object type
		 * @param state - State to set
		 * @param state.success - Success state
		 * @param state.error - Error state
		 */
		setState(
			type: Type,
			{ success, error }: {
                success: State['success'][Type];
                error: State['errors'][Type];
            },
		) {
			if (success !== undefined) {
				this.success = {
					...this.success,
					[type]: success,
				}
			}
			if (error !== undefined) {
				this.errors = {
					...this.errors,
					[type]: error,
				}
			}
		},

		/**
		 * Copy an existing object
		 * @param type - Object type
		 * @param id - Object ID to copy
		 */
		async copyObject(type: Type, id: Id) {
			try {
				this.setLoading(`${type}_${id}_copy`, true)
				this.setError(`${type}_${id}_copy`, null)
				this.setState(type, { success: null, error: null })

				// Ensure settings are loaded first
				if (!this.settings) {
					await this.fetchSettings()
				}

				// Get the original object
				const originalObject = this.objects[type]?.[id]
				if (!originalObject) {
					throw new Error(`Object ${id} of type ${type} not found`)
				}

				// Create a copy of the object without the id
				const objectData = { ...originalObject }
				delete objectData.id

				// Add "Copy of" to the title or name
				if (objectData.title) {
					objectData.title = `Kopie van ${objectData.title}`
				} else if (objectData.name) {
					objectData.name = `Kopie van ${objectData.name}`
				}

				// Create the new object
				const newObject = await this.createObject(type, objectData)

				// Set success state
				this.setState(type, { success: true, error: null })

				return newObject
			} catch (error) {
				console.error(`Error copying ${type} object:`, error)
				const errorMessage = error instanceof Error ? error.message : 'Unknown error'
				this.setError(`${type}_${id}_copy`, errorMessage)
				this.setState(type, { success: false, error: errorMessage })
				throw error
			} finally {
				this.setLoading(`${type}_${id}_copy`, false)
			}
		},
	},
})
