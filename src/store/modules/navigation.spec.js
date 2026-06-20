/* eslint-disable no-console */
import { setActivePinia, createPinia } from 'pinia'

import { useNavigationStore } from './navigation.ts'

describe('Navigation Store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('set modal correctly', () => {
		const store = useNavigationStore()

		store.setModal('editPublication')
		expect(store.modal).toBe('editPublication')

		store.setModal('editCatalogi')
		expect(store.modal).toBe('editCatalogi')

		store.setModal('editMetadata')
		expect(store.modal).toBe('editMetadata')
	})

	it('set dialog correctly', () => {
		const store = useNavigationStore()

		store.setDialog('deletePublication')
		expect(store.dialog).toBe('deletePublication')

		store.setDialog('deleteCatalogi')
		expect(store.dialog).toBe('deleteCatalogi')

		store.setDialog('deleteMetadata')
		expect(store.dialog).toBe('deleteMetadata')
	})
})
