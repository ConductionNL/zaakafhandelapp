/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useContactMomentStore } from './contactmoment.js'
import { ContactMoment, mockContactMoment } from '../../entities/index.js'

describe('Contact Moment Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('sets contact moment item correctly', () => {
		const store = useContactMomentStore()

		store.setContactMomentItem(mockContactMoment()[0])

		expect(store.contactMomentItem).toBeInstanceOf(ContactMoment)
		expect(store.contactMomentItem).toEqual(mockContactMoment()[0])

		expect(store.contactMomentItem.validate().success).toBe(true)
	})

	it('sets contact moment list correctly', () => {
		const store = useContactMomentStore()

		store.setContactMomentenList(mockContactMoment())

		expect(store.contactMomentenList).toHaveLength(mockContactMoment().length)

		store.contactMomentenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(ContactMoment)
			expect(item).toEqual(mockContactMoment()[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
