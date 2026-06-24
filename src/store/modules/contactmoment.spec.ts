/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useContactMomentStore } from './contactmoment.js'
import { ContactMoment, mockContactMoment } from '../../entities/index.js'

describe('Contact Moment Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	// mockContactMoment() builds entities with `startDate: new Date().toISOString()`,
	// so snapshot it once and reuse — otherwise repeated calls yield timestamps that
	// are ~1ms apart and produce spurious deep-equality mismatches.
	it('sets contact moment item correctly', () => {
		const store = useContactMomentStore()
		const mocks = mockContactMoment()

		store.setContactMomentItem(mocks[0])

		expect(store.contactMomentItem).toBeInstanceOf(ContactMoment)
		expect(store.contactMomentItem).toEqual(mocks[0])

		expect(store.contactMomentItem.validate().success).toBe(true)
	})

	it('sets contact moment list correctly', () => {
		const store = useContactMomentStore()
		const mocks = mockContactMoment()

		store.setContactMomentenList(mocks)

		expect(store.contactMomentenList).toHaveLength(mocks.length)

		store.contactMomentenList.forEach((item, index) => {
			expect(item).toBeInstanceOf(ContactMoment)
			expect(item).toEqual(mocks[index])
			expect(item.validate().success).toBe(true)
		})
	})
})
