import { createApp } from 'vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import OpenZakenWidget from './views/widgets/OpenZakenWidget.vue'

// Library CSS (CnDataTable styles live in the lib's central CSS, not in the
// SFC) — the widget bundle loads standalone on the Dashboard, without main.js.
import '@conduction/nextcloud-vue/css/index.css'

OCA.Dashboard.register('zaakAfhandelApp_openzaak_widget', (el, { widget }) => {
	// Vue 3: one app instance per widget mount. `mount(el)` renders INSIDE the
	// element Nextcloud hands us, where Vue 2's `$mount(el)` REPLACED it —
	// rendering inside is what the Dashboard API expects, so the container's
	// own styling survives.
	const app = createApp(OpenZakenWidget, { title: widget.title })

	// `t` / `n` were previously referenced here without being imported, so they
	// resolved to Nextcloud's window globals. Import them explicitly — the
	// flat-config lint layer flags the implicit global, and the explicit form
	// is what every other entry point uses.
	app.mixin({ methods: { t, n } })

	app.mount(el)
})
