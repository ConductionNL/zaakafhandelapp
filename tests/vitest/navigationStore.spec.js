/**
 * SPDX-FileCopyrightText: 2026 Conduction / ZaakAfhandelApp Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Unit tests for the navigation/ui Pinia store (src/store/modules/navigation.ts).
 * Focus on the behaviour the existing Jest spec does NOT cover: the
 * transferData consume-once contract and the view-modal / dialog state, so
 * the two suites are complementary rather than duplicative.
 */

import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useNavigationStore } from '../../src/store/modules/navigation.ts'

describe('navigation store — modal & dialog state', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		vi.spyOn(console, 'log').mockImplementation(() => {})
	})

	it('defaults all surfaces to null', () => {
		const store = useNavigationStore()
		expect(store.modal).toBeNull()
		expect(store.viewModal).toBeNull()
		expect(store.dialog).toBeNull()
		expect(store.transferData).toBeNull()
	})

	it('setViewModal sets the active view modal', () => {
		const store = useNavigationStore()
		store.setViewModal('viewContactMoment')
		expect(store.viewModal).toBe('viewContactMoment')
	})

	it('setModal and setDialog set their respective surfaces independently', () => {
		const store = useNavigationStore()
		store.setModal('zaakForm')
		store.setDialog('deleteBesluit')
		expect(store.modal).toBe('zaakForm')
		expect(store.dialog).toBe('deleteBesluit')
		// clearing the modal leaves the dialog intact
		store.setModal(null)
		expect(store.modal).toBeNull()
		expect(store.dialog).toBe('deleteBesluit')
	})
})

describe('navigation store — transferData consume-once', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		vi.spyOn(console, 'log').mockImplementation(() => {})
	})

	it('setTransferData stores the payload', () => {
		const store = useNavigationStore()
		store.setTransferData('payload-1')
		expect(store.transferData).toBe('payload-1')
	})

	it('getTransferData returns the payload AND clears it (read-once)', () => {
		const store = useNavigationStore()
		store.setTransferData('payload-2')
		expect(store.getTransferData()).toBe('payload-2')
		// second read yields null — the data was consumed
		expect(store.transferData).toBeNull()
		expect(store.getTransferData()).toBeNull()
	})
})
