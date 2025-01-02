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

		store.setDocumentItem(mockDocumentData()[0])

		expect(store.documentItem).toBeInstanceOf(Document)
		expect(store.documentItem).toEqual(mockDocumentData()[0])

		expect(store.documentItem.validate().success).toBe(true)
	})

	it('sets documenten list correctly', () => {
		const store = useDocumentStore()

		store.setDocumentenList(mockDocumentData())

		expect(store.documentenList).toHaveLength(mockDocumentData().length)

		store.documentenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Document)
			expect(item).toEqual(mockDocumentData()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
