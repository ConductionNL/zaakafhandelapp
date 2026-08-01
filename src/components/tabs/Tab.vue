<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Vue 3 replacement for bootstrap-vue's `<BTab>`. See Tabs.vue for why this
 pair exists.

 Contract kept from BTab (only the parts the app used):
   - `title` prop, or a `#title` slot for rich titles (the closable
     contactmoment tabs put an NcButton in there)
   - `active` prop, honoured both initially AND on change — ContactMomentenForm
     binds `:active="selectedContactMoment === i"` and expects the strip to
     follow it
   - a `click` event fired when the user activates this tab

 The panel is kept in the DOM and hidden with `display: none` rather than
 `v-if`-ed away, matching BTab's behaviour: several panels fetch on mount and
 destroying them on every tab switch would refire those requests.
-->

<template>
	<div class="zaa-tab-pane"
		role="tabpanel"
		:style="visible ? null : { display: 'none' }">
		<slot />
	</div>
</template>

<script>
import { computed, defineComponent, getCurrentInstance, inject, onBeforeUnmount, onMounted, watch } from 'vue'
import { TABS_INJECTION_KEY } from './tabsKey.js'

export default defineComponent({
	name: 'Tab',

	props: {
		/** Plain-text tab title. Ignored when a `#title` slot is supplied. */
		title: {
			type: String,
			default: '',
		},
		/** Select this tab. Honoured on mount and on every later change. */
		active: {
			type: Boolean,
			default: false,
		},
	},

	emits: ['click'],

	setup(props, { slots, emit }) {
		const tabsApi = inject(TABS_INJECTION_KEY, null)
		const uid = getCurrentInstance().uid

		const entry = {
			uid,
			// Called from the PARENT's render effect, so reading `props.title`
			// (or invoking the slot) here is what keeps a computed title
			// reactive in the nav strip.
			titleRender: () => (slots.title ? slots.title() : props.title),
			onActivate: () => {
				tabsApi?.select(uid)
				emit('click')
			},
			get active() {
				return props.active
			},
		}

		onMounted(() => tabsApi?.register(entry))
		onBeforeUnmount(() => tabsApi?.unregister(uid))

		watch(() => props.active, (isActive) => {
			if (isActive) {
				tabsApi?.select(uid)
			}
		})

		// Rendered outside a <Tabs> parent (none today, but a stray usage
		// should show its content rather than vanish silently).
		const visible = computed(() => (tabsApi ? tabsApi.isActive(uid) : true))

		return { visible }
	},
})
</script>
