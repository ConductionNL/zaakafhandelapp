/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useDocumentStore } from './documenten.js'
import { Document, mockDocumentData } from '../../entities/index.js'

describe('Document Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets document item correctly', () => {
		const store = useDocumentStore()
		const data = mockDocumentData()[0]

		store.setDocumentItem(data)

		expect(store.documentItem).toBeInstanceOf(Document)
		// The Document entity back-fills optional fields with null, so it's a
		// superset of the mock payload — assert containment, not strict equality.
		expect(store.documentItem).toEqual(expect.objectContaining(data))

		expect(store.documentItem.validate().success).toBe(true)
	})

	it('sets documenten list correctly', () => {
		const store = useDocumentStore()
		const data = mockDocumentData()

		store.setDocumentenList(data)

		expect(store.documentenList).toHaveLength(data.length)

		store.documentenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Document)
			expect(item).toEqual(expect.objectContaining(data[index]))
			expect(item.validate().success).toBe(true)
		})
	})
})
