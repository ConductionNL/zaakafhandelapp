/**
 * Deadline-urgency derivation for a zaak (zaak-termijn-monitoring REQ-003).
 *
 * A single shared derivation consumed by every UI surface (case lists, the
 * werkvoorraad and the dashboard widgets) so they never disagree:
 *
 *  - `verlopen`        — an open zaak whose `uiterlijkeEinddatumAfdoening` is in the past;
 *  - `bijna-verlopen`  — within the lead window (default 7 days) of the statutory
 *                        deadline, or already past the planned `einddatumGepland`;
 *  - `op-tijd`         — an open, dated zaak that is neither overdue nor approaching;
 *  - `null`            — a closed zaak (`einddatum` set) or one without termijn fields.
 *
 * @spec openspec/specs/zaak-termijn-monitoring/spec.md#REQ-003
 */

const MS_PER_DAY = 1000 * 60 * 60 * 24

/**
 * Parse a date string to a midnight-anchored timestamp, or null when invalid/empty.
 *
 * @param {string} [value] The date string.
 * @return {number | null} The timestamp at local midnight, or null.
 */
function toMidnight(value) {
	if (!value) {
		return null
	}
	const d = new Date(value)
	if (isNaN(d.getTime())) {
		return null
	}
	d.setHours(0, 0, 0, 0)
	return d.getTime()
}

/**
 * Derive the deadline-urgency state of a zaak.
 *
 * @param {{ einddatum?: string, einddatumGepland?: string, uiterlijkeEinddatumAfdoening?: string }} zaak The zaak.
 * @param {Date} [today] The reference date (default: now).
 * @param {number} [leadDays] The approaching-deadline lead window in days (default 7).
 * @return {('op-tijd' | 'bijna-verlopen' | 'verlopen' | null)} The urgency state, or null for closed/undated zaken.
 *
 * @spec openspec/specs/zaak-termijn-monitoring/spec.md#REQ-003
 */
export function deriveZaakUrgency(zaak, today = new Date(), leadDays = 7) {
	// Closed zaken carry no urgency.
	if (zaak?.einddatum) {
		return null
	}

	const uiterste = toMidnight(zaak?.uiterlijkeEinddatumAfdoening)
	const gepland = toMidnight(zaak?.einddatumGepland)

	// No termijn fields at all → no urgency.
	if (uiterste === null && gepland === null) {
		return null
	}

	const now = new Date(today)
	now.setHours(0, 0, 0, 0)
	const nowTs = now.getTime()

	if (uiterste !== null && uiterste < nowTs) {
		return 'verlopen'
	}

	if (uiterste !== null) {
		const daysLeft = Math.round((uiterste - nowTs) / MS_PER_DAY)
		if (daysLeft <= leadDays) {
			return 'bijna-verlopen'
		}
	}

	// Past the planned (service) deadline but not yet the statutory one.
	if (gepland !== null && gepland < nowTs) {
		return 'bijna-verlopen'
	}

	return 'op-tijd'
}

/**
 * The user-facing English label key for an urgency state (use as the i18n key).
 *
 * @param {('op-tijd' | 'bijna-verlopen' | 'verlopen' | null)} urgency The urgency state.
 * @return {string} The English label, or '' for null.
 *
 * @spec openspec/specs/zaak-termijn-monitoring/spec.md#REQ-003
 */
export function urgencyLabel(urgency) {
	switch (urgency) {
		case 'verlopen':
			return 'Overdue'
		case 'bijna-verlopen':
			return 'Deadline approaching'
		case 'op-tijd':
			return 'On time'
		default:
			return ''
	}
}
