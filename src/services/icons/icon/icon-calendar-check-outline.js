import { getTheme } from '../../getTheme.js'

/**
 * Returns the correct 'calendar-check-outline' icon based on the theme as a class name.
 *
 * this class name can be put into a component that accepts an 'icon' prop
 * @return {string}
  *
  * @spec openspec/specs/ui-search-navigation/spec.md#REQ-005
 */
export function iconCalendarCheckOutline() {
	const theme = getTheme()

	return theme === 'light' ? 'icon-calendar-check-outline-dark' : 'icon-calendar-check-outline-light'
}
