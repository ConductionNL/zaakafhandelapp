import { getTheme } from '../../getTheme.js'

/**
 * Returns the correct 'card account phone outline' icon based on the theme as a class name.
 *
 * this class name can be put into a component that accepts an 'icon' prop
 * @return {string}
 */
export function iconCardAccountPhoneOutline() {
	const theme = getTheme()

	return theme === 'light' ? 'icon-card-account-phone-outline-dark' : 'icon-card-account-phone-outline-light'
}
