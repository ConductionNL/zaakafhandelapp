/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useZaakTypeStore } from './zaakTypen.js'
import { ZaakType, mockZaakType } from '../../entities/index.js'

describe('ZaakTypen Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets zaakType item correctly', () => {
		const store = useZaakTypeStore()

		store.setZaakTypeItem(mockZaakType()[0])

		expect(store.zaakTypeItem).toBeInstanceOf(ZaakType)
		expect(store.zaakTypeItem).toEqual(mockZaakType()[0])

		expect(store.zaakTypeItem.validate().success).toBe(true)
	})

	it('sets zaakTypen list correctly', () => {
		const store = useZaakTypeStore()

		store.setZaakTypeList(mockZaakType())

		expect(store.zaakTypeList).toHaveLength(mockZaakType().length)

		store.zaakTypeList.forEach((item, index) => {
			expect(item).toBeInstanceOf(ZaakType)
			expect(item).toEqual(mockZaakType()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
