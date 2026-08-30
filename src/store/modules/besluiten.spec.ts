import { createPinia, setActivePinia } from 'pinia'
import { Besluit, mockBesluit } from '../../entities/index.js'
import { useBesluitStore } from './besluiten.js'

describe('Besluit Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets besluit item correctly', () => {
		const store = useBesluitStore()

		store.setBesluitItem(mockBesluit()[0])

		expect(store.besluitItem).toBeInstanceOf(Besluit)
		expect(store.besluitItem).toEqual(mockBesluit()[0])

		expect(store.besluitItem.validate().success).toBe(true)
	})
})
