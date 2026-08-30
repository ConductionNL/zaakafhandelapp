import { createPinia, setActivePinia } from 'pinia'
import { mockZaakType, ZaakType } from '../../entities/index.js'
import { useZaakTypeStore } from './zaakTypen'

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
