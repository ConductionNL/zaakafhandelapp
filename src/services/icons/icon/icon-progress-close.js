import { getTheme } from '../../getTheme.js'

/**
 * Returns the correct 'progress close' icon based on the theme as a class name.
 *
 * this class name can be put into a component that accepts an 'icon' prop
 * @return {string}
 */
export function iconProgressClose() {
	const theme = getTheme()

	return theme === 'light' ? 'icon-progress-close-dark' : 'icon-progress-close-light'
}
