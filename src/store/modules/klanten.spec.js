/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useKlantStore } from './klanten.js'
import { Klant, mockKlant } from '../../entities/index.js'

describe('Klant Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets klant item correctly', () => {
		const store = useKlantStore()

		store.setKlantItem(mockKlant()[0])

		expect(store.klantItem).toBeInstanceOf(Klant)
		expect(store.klantItem).toEqual(mockKlant()[0])

		expect(store.klantItem.validate().success).toBe(true)
	})

	it('sets zaken list correctly', () => {
		const store = useKlantStore()

		store.setKlantenList(mockKlant())

		expect(store.klantenList).toHaveLength(mockKlant().length)

		store.klantenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Klant)
			expect(item).toEqual(mockKlant()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
