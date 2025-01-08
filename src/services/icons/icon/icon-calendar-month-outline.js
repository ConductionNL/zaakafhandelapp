import { getTheme } from '../../getTheme.js'

/**
 * Returns the correct 'calendar month outline' icon based on the theme as a class name.
 *
 * this class name can be put into a component that accepts an 'icon' prop
 * @return {string}
 */
export function iconCalendarMonthOutline() {
	const theme = getTheme()

	return theme === 'light' ? 'icon-calendar-month-outline-dark' : 'icon-calendar-month-outline-light'
}
