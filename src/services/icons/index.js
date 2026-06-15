/*
 * This file serves as a central hub for CSS based icon management in the application.
 * It exports functions that return the appropriate icon class names based on the current theme.
 *
 * The actual icon definitions (CSS classes) are located in '/css/icons.css'.
 * To add new icons:
 * 1. Add the icon definitions to '/css/icons.css'
 * 2. Create a new icon function file in './icon/' directory
 * 3. Import and export the icon function here
 *
 * Each icon function returns a class name string that can be used in components
 * that accept an 'icon' prop. The returned class name will automatically handle
 * light/dark theme variants.
 */

import { iconProgressClose as _iconProgressClose } from './icon/icon-progress-close.js'
import { iconPencil as _iconPencil } from './icon/icon-pencil.js'
import { iconCalendarCheckOutline as _iconCalendarCheckOutline } from './icon/icon-calendar-check-outline.js'
import { iconCalendarMonthOutline as _iconCalendarMonthOutline } from './icon/icon-calendar-month-outline.js'
import { iconCardAccountPhoneOutline as _iconCardAccountPhoneOutline } from './icon/icon-card-account-phone-outline.js'
import { iconBriefcaseAccountOutline as _iconBriefcaseAccountOutline } from './icon/icon-briefcase-account-outline.js'

export const iconProgressClose = _iconProgressClose()
export const iconPencil = _iconPencil()
export const iconCalendarCheckOutline = _iconCalendarCheckOutline()
export const iconCalendarMonthOutline = _iconCalendarMonthOutline()
export const iconCardAccountPhoneOutline = _iconCardAccountPhoneOutline()
export const iconBriefcaseAccountOutline = _iconBriefcaseAccountOutline()
