/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useBerichtStore } from './berichten.js'
import { Bericht, mockBericht } from '../../entities/index.js'

describe('Bericht Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets zaak item correctly', () => {
		const store = useBerichtStore()

		store.setBerichtItem(mockBericht()[0])

		expect(store.berichtItem).toBeInstanceOf(Bericht)
		expect(store.berichtItem).toEqual(mockBericht()[0])

		expect(store.berichtItem.validate().success).toBe(true)
	})

	it('sets zaken list correctly', () => {
		const store = useBerichtStore()

		store.setBerichtList(mockBericht())

		expect(store.berichtList).toHaveLength(mockBericht().length)

		store.berichtList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Bericht)
			expect(item).toEqual(mockBericht()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
