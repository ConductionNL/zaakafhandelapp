/**
 * SPDX-FileCopyrightText: 2026 Conduction / ZaakAfhandelApp Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Unit tests for the deadline-urgency derivation (src/services/zaakUrgency.ts,
 * zaak-termijn-monitoring REQ-003): boundary dates (deadline today, +7d, -1d),
 * closed/undated zaken, the planned-deadline warning, and the label mapping.
 */

import { describe, it, expect } from 'vitest'
import { deriveZaakUrgency, urgencyLabel } from '../../src/services/zaakUrgency.js'

const TODAY = new Date('2026-06-15T12:00:00Z')

describe('deriveZaakUrgency', () => {
	it('returns verlopen for an open zaak past its statutory deadline', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-14' }, TODAY)).toBe('verlopen')
	})

	it('returns bijna-verlopen within the default 7-day lead window', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-18' }, TODAY)).toBe('bijna-verlopen')
	})

	it('treats the deadline today as approaching', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-15' }, TODAY)).toBe('bijna-verlopen')
	})

	it('treats exactly +7 days as approaching (boundary)', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-22' }, TODAY)).toBe('bijna-verlopen')
	})

	it('returns op-tijd for a deadline beyond the lead window', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-07-30' }, TODAY)).toBe('op-tijd')
	})

	it('flags bijna-verlopen when past the planned (service) deadline only', () => {
		expect(deriveZaakUrgency(
			{ uiterlijkeEinddatumAfdoening: '2026-07-30', einddatumGepland: '2026-06-10' },
			TODAY,
		)).toBe('bijna-verlopen')
	})

	it('returns null for a closed zaak even when overdue', () => {
		expect(deriveZaakUrgency(
			{ einddatum: '2026-06-10', uiterlijkeEinddatumAfdoening: '2026-06-01' },
			TODAY,
		)).toBeNull()
	})

	it('returns null for a zaak without termijn fields', () => {
		expect(deriveZaakUrgency({}, TODAY)).toBeNull()
	})

	it('honours a custom lead window', () => {
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-25' }, TODAY, 14)).toBe('bijna-verlopen')
		expect(deriveZaakUrgency({ uiterlijkeEinddatumAfdoening: '2026-06-25' }, TODAY, 3)).toBe('op-tijd')
	})
})

describe('urgencyLabel', () => {
	it('maps each state to an English source-string key', () => {
		expect(urgencyLabel('verlopen')).toBe('Overdue')
		expect(urgencyLabel('bijna-verlopen')).toBe('Deadline approaching')
		expect(urgencyLabel('op-tijd')).toBe('On time')
		expect(urgencyLabel(null)).toBe('')
	})
})
