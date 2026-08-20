/**
 * Converts a given date string or Date object to a valid ISO string.
 *
 * this function can double as a validator for ISO / date strings
 *
 * If the dateString is valid it will return the ISO string,
 * if it is not a valid dateString it will return null.
 *
 * @param { string | Date } dateString The date string or Date object to be converted.
 * @return { string | null } The ISO string representation of the date or null.
 *
 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-004
 */
export default function getValidISOstring(dateString) {
	const date = new Date(dateString)

	if (!isNaN(date.getTime())) {
		return date.toISOString()
	} else {
		return null
	}
}
