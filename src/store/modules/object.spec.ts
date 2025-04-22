/* eslint-disable no-console */
import { createPinia, setActivePinia } from 'pinia'
import { useObjectStore } from './object.js'

/**
 * Mock settings response
 */
const mockSettings = {
	objectTypes: ['klant', 'item', 'skill'],
	configuration: {
		klant_source: 'openregister',
		klant_schema: '105',
		klant_register: '20',
		item_source: 'openregister',
		item_schema: '109',
		item_register: '20',
		skill_source: 'openregister',
		skill_schema: '110',
		skill_register: '20',
	},
}

/**
 * Mock collection response
 */
const mockCollection = {
	total: 2,
	page: 1,
	perPage: 10,
	results: [
		{ id: '1', name: 'Test 1' },
		{ id: '2', name: 'Test 2' },
	],
}

/**
 * Mock single object response
 */
const mockObject = {
	id: '1',
	name: 'Test Object',
	description: 'Test Description',
}

/**
 * Mock related data responses
 */
const mockRelatedData = {
	logs: {
		total: 1,
		page: 1,
		perPage: 10,
		results: [{ id: '1', action: 'create', timestamp: '2024-04-13T00:00:00Z' }],
	},
	uses: {
		total: 1,
		page: 1,
		perPage: 10,
		results: [{ id: '1', usedBy: 'klant-1', timestamp: '2024-04-13T00:00:00Z' }],
	},
	used: {
		total: 1,
		page: 1,
		perPage: 10,
		results: [{ id: '1', usedIn: 'event-1', timestamp: '2024-04-13T00:00:00Z' }],
	},
	files: {
		total: 1,
		page: 1,
		perPage: 10,
		results: [{ id: '1', filename: 'test.pdf', size: 1024 }],
	},
}

// Mock fetch globally
const mockFetch = jest.fn() as jest.MockedFunction<typeof fetch>
global.fetch = mockFetch

describe('ObjectStore', () => {
	let store: ReturnType<typeof useObjectStore>

	beforeEach(() => {
		setActivePinia(createPinia())
		store = useObjectStore()
		// Reset fetch mock
		mockFetch.mockReset()
	})

	describe('Settings', () => {
		it('fetches settings successfully', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockSettings),
			} as Response)

			await store.fetchSettings()

			expect(store.settings).toEqual(mockSettings)
			expect(store.objectTypes).toEqual(['klant', 'item', 'skill'])
			expect(mockFetch).toHaveBeenCalledWith('/index.php/apps/opencatalogi/api/settings')
		})

		it('handles settings fetch error', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: false,
			} as Response)

			await expect(store.fetchSettings()).rejects.toThrow('Failed to fetch settings')
		})
	})

	describe('Schema Configuration', () => {
		beforeEach(async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockSettings),
			} as Response)
			await store.fetchSettings()
		})

		it('gets schema configuration for valid object type', () => {
			const config = store.getSchemaConfig('klant')
			expect(config).toEqual({
				source: 'openregister',
				schema: '105',
				register: '20',
			})
		})

		it('throws error for invalid object type', () => {
			// @ts-expect-error - Invalid object type
			expect(() => store.getSchemaConfig('invalid')).toThrow('Invalid configuration for object type: invalid')
		})
	})

	describe('Collection Operations', () => {
		beforeEach(async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockSettings),
			} as Response)
			await store.fetchSettings()
		})

		it('fetches collection successfully', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockCollection),
			} as Response)

			await store.fetchCollection('klant')

			expect(store.collections.klant).toEqual(mockCollection.results)
			expect(store.objects.klant).toEqual({
				1: { id: '1', name: 'Test 1' },
				2: { id: '2', name: 'Test 2' },
			})
			expect(store.isLoading('klant')).toBe(false)
			expect(store.getError('klant')).toBeNull()
		})

		it('handles collection fetch error', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: false,
			} as Response)

			await expect(store.fetchCollection('klant')).rejects.toThrow('Failed to fetch klant collection')
			expect(store.isLoading('klant')).toBe(false)
			expect(store.getError('klant')).toBe('Failed to fetch klant collection')
		})
	})

	describe('Single Object Operations', () => {
		beforeEach(async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockSettings),
			} as Response)
			await store.fetchSettings()
		})

		it('fetches single object successfully', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockObject),
			} as Response)

			await store.fetchObject('klant', '1')

			expect(store.objects.klant['1']).toEqual(mockObject)
			expect(store.isLoading('klant_1')).toBe(false)
			expect(store.getError('klant_1')).toBeNull()
		})

		it('creates object successfully', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockObject),
			} as Response)

			const newObject = await store.createObject('klant', { name: 'New klant' })

			expect(newObject).toEqual(mockObject)
			expect(store.objects.klant['1']).toEqual(mockObject)
			expect(store.isLoading('klant_create')).toBe(false)
			expect(store.getError('klant_create')).toBeNull()
		})

		it('updates object successfully', async () => {
			const updatedObject = { ...mockObject, name: 'Updated Name' }
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(updatedObject),
			} as Response)

			const result = await store.updateObject('klant', '1', { name: 'Updated Name' })

			expect(result).toEqual(updatedObject)
			expect(store.objects.klant['1']).toEqual(updatedObject)
			expect(store.isLoading('klant_1')).toBe(false)
			expect(store.getError('klant_1')).toBeNull()
		})

		it('deletes object successfully', async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
			} as Response)

			// Add object to store first
			store.objects.klant = { 1: mockObject }
			// a collection type is expected to be a object with "results" as an array
			store.collections.klant = { results: [mockObject] }

			await store.deleteObject('klant', '1')

			expect(store.objects.klant['1']).toBeUndefined()
			expect(store.collections.klant).toEqual([])
			expect(store.isLoading('klant_1')).toBe(false)
			expect(store.getError('klant_1')).toBeNull()
		})
	})

	describe('Active Object Operations', () => {
		beforeEach(async () => {
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockSettings),
			} as Response)
			await store.fetchSettings()

			// Mock related data fetches
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.logs),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.uses),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.used),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.files),
			} as Response)
		})

		it('sets active object and fetches related data', async () => {
			await store.setActiveObject('klant', mockObject)

			expect(store.activeObjects.klant).toEqual(mockObject)
			expect(store.relatedData.klant).toEqual({
				logs: mockRelatedData.logs,
				uses: mockRelatedData.uses,
				used: mockRelatedData.used,
				files: mockRelatedData.files,
			})
		})

		it('clears active object and related data', async () => {
			await store.setActiveObject('klant', mockObject)
			store.clearActiveObject('klant')

			expect(store.activeObjects.klant).toBeUndefined()
			expect(store.relatedData.klant).toBeUndefined()
		})

		it('updates active object when fetching same object', async () => {
			await store.setActiveObject('klant', mockObject)

			const updatedObject = { ...mockObject, name: 'Updated Name' }
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(updatedObject),
			} as Response)

			// Mock related data fetches again
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.logs),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.uses),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.used),
			} as Response)
			mockFetch.mockResolvedValueOnce({
				ok: true,
				json: () => Promise.resolve(mockRelatedData.files),
			} as Response)

			await store.fetchObject('klant', '1')

			expect(store.activeObjects.klant).toEqual(updatedObject)
		})

		it('clears active object when deleting it', async () => {
			await store.setActiveObject('klant', mockObject)

			mockFetch.mockResolvedValueOnce({
				ok: true,
			} as Response)

			await store.deleteObject('klant', '1')

			expect(store.activeObjects.klant).toBeUndefined()
			expect(store.relatedData.klant).toBeUndefined()
		})

		it('handles related data fetch error', async () => {
			mockFetch.mockReset()
			mockFetch.mockRejectedValueOnce(new Error('Network error'))

			await expect(store.fetchRelatedData('klant', '1', 'logs')).rejects.toThrow()
			expect(store.getError('klant_1_logs')).toBe('Network error')
			expect(store.isLoading('klant_1_logs')).toBe(false)
		})
	})
})
