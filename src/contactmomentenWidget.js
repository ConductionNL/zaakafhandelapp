import { translatePlural as n, translate as t } from '@nextcloud/l10n'
import { createApp } from 'vue'
import ContactMomentenWidget from './views/widgets/ContactMomentenWidget.vue'

// Library CSS (CnDataTable styles live in the lib's central CSS, not in the
// SFC) — the widget bundle loads standalone on the Dashboard, without main.js.
import '@conduction/nextcloud-vue/css/index.css'

// NOTE: the `tooltip` directive registration that used to live here is gone.
// @nextcloud/vue v9 REMOVED the Tooltip directive — its dist ships only
// `directives/Focus` and `directives/Linkify`, so
// `@nextcloud/vue/dist/Directives/Tooltip.js` no longer resolves. The handful
// of `v-tooltip` bindings in this app were converted to native `title`
// attributes; an unregistered directive is only a dev-mode warning, so the
// tooltip would otherwise have disappeared silently in production.

OCA.Dashboard.register(
	'zaakAfhandelApp_contactmomenten_widget',
	(el, { widget }) => {
		// Vue 3: one app instance per widget mount. `mount(el)` renders INSIDE the
		// element Nextcloud hands us, where Vue 2's `$mount(el)` REPLACED it.
		const app = createApp(ContactMomentenWidget, { title: widget.title })

		// `t` / `n` were previously referenced here without being imported, so they
		// resolved to Nextcloud's window globals. Import them explicitly.
		app.mixin({ methods: { t, n } })

		app.mount(el)
	},
)
