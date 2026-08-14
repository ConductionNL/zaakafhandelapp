import { createPinia, setActivePinia } from 'pinia'
import { mockTaak, Taak } from '../../entities/index.js'
import { useTaakStore } from './taak.js'

describe('Taak Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets taak item correctly', () => {
		const store = useTaakStore()

		store.setTaakItem(mockTaak()[0])

		expect(store.taakItem).toBeInstanceOf(Taak)
		expect(store.taakItem).toEqual(mockTaak()[0])

		expect(store.taakItem.validate().success).toBe(true)
	})

	it('sets taken list correctly', () => {
		const store = useTaakStore()

		store.setTakenList(mockTaak())

		expect(store.takenList).toHaveLength(mockTaak().length)

		store.takenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Taak)
			expect(item).toEqual(mockTaak()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
