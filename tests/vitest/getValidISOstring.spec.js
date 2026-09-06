/**
 * SPDX-FileCopyrightText: 2026 Conduction / ZaakAfhandelApp Contributors
 * SPDX-License-Identifier: EUPL-1.2
 *
 * Unit tests for getValidISOstring (src/services/getValidISOstring.js) — the
 * date→ISO converter that the forms also use as a validator: a valid date
 * (string or Date) returns its canonical ISO string, anything unparseable
 * returns null.
 */

import { describe, expect, it } from 'vitest'
import getValidISOstring from '../../src/services/getValidISOstring.js'

describe('getValidISOstring', () => {
	it('returns the canonical ISO string for a valid date string', () => {
		expect(getValidISOstring('2026-06-11T10:30:00Z')).toBe(
			'2026-06-11T10:30:00.000Z',
		)
	})

	it('normalises a timezone offset to UTC', () => {
		// 12:30+02:00 == 10:30Z
		expect(getValidISOstring('2026-06-11T12:30:00+02:00')).toBe(
			'2026-06-11T10:30:00.000Z',
		)
	})

	it('accepts a Date instance', () => {
		const d = new Date('2026-01-02T03:04:05Z')
		expect(getValidISOstring(d)).toBe('2026-01-02T03:04:05.000Z')
	})

	it('accepts a date-only string (midnight UTC)', () => {
		expect(getValidISOstring('2026-06-11')).toBe('2026-06-11T00:00:00.000Z')
	})

	it('returns null for unparseable input', () => {
		expect(getValidISOstring('not-a-date')).toBeNull()
		expect(getValidISOstring('')).toBeNull()
		expect(getValidISOstring(undefined)).toBeNull()
		expect(getValidISOstring('2026-13-40T99:99:99Z')).toBeNull()
	})

	it('inherits the new Date(null) === epoch JS quirk (documented, not null)', () => {
		// `new Date(null)` coerces to 0 (epoch) rather than Invalid Date, so the
		// helper returns the epoch ISO. Callers should guard null before calling.
		expect(getValidISOstring(null)).toBe('1970-01-01T00:00:00.000Z')
	})
})
