/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useZaakStore } from './zaken.js'
import { Zaak, mockZaak } from '../../entities/index.js'

describe('Zaak Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets zaak item correctly', () => {
		const store = useZaakStore()

		store.setZaakItem(mockZaak()[0])

		expect(store.zaakItem).toBeInstanceOf(Zaak)
		expect(store.zaakItem).toEqual(mockZaak()[0])

		expect(store.zaakItem.validate().success).toBe(true)
	})

	it('sets zaken list correctly', () => {
		const store = useZaakStore()

		store.setZakenList(mockZaak())

		expect(store.zakenList).toHaveLength(mockZaak().length)

		store.zakenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Zaak)
			expect(item).toEqual(mockZaak()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
