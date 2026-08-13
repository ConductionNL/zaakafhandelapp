/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useMedewerkerStore } from './medewerkers.js'
import { Medewerker, mockMedewerker } from '../../entities/index.js'

describe('Medewerker Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets medewerker item correctly', () => {
		const store = useMedewerkerStore()

		store.setMedewerkerItem(mockMedewerker()[0])

		expect(store.medewerkerItem).toBeInstanceOf(Medewerker)
		expect(store.medewerkerItem).toEqual(mockMedewerker()[0])

		expect(store.medewerkerItem.validate().success).toBe(true)
	})

	it('sets medewerkers list correctly', () => {
		const store = useMedewerkerStore()

		store.setMedewerkersList(mockMedewerker())

		expect(store.medewerkersList).toHaveLength(mockMedewerker().length)

		store.medewerkersList.forEach((item, index) => {
			expect(item).toBeInstanceOf(Medewerker)
			expect(item).toEqual(mockMedewerker()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
