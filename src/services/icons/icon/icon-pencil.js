import { getTheme } from '../../getTheme.js'

/**
 * Returns the correct 'pencil' icon based on the theme as a class name.
 *
 * this class name can be put into a component that accepts an 'icon' prop
 * @return {string}
 */
export function iconPencil() {
	const theme = getTheme()

	return theme === 'light' ? 'icon-pencil-dark' : 'icon-pencil-light'
}
