/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useZaakTypenStore } from './zaakTypen.js'
import { ZaakType, mockZaakTypen } from '../../entities/index.js'

describe('ZaakTypen Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets zaakType item correctly', () => {
		const store = useZaakTypenStore()

		store.setZaakTypeItem(mockZaakTypen()[0])

		expect(store.zaakTypeItem).toBeInstanceOf(ZaakType)
		expect(store.zaakTypeItem).toEqual(mockZaakTypen()[0])

		expect(store.zaakTypeItem.validate().success).toBe(true)
	})

	it('sets zaakTypen list correctly', () => {
		const store = useZaakTypenStore()

		store.setZaakTypeList(mockZaakTypen())

		expect(store.zaakTypeList).toHaveLength(mockZaakTypen().length)

		store.zaakTypeList.forEach((item, index) => {
			expect(item).toBeInstanceOf(ZaakType)
			expect(item).toEqual(mockZaakTypen()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
