import Vue from 'vue'
import ContactMomentenWidget from './views/widgets/ContactMomentenWidget.vue'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'

// Library CSS (CnDataTable styles live in the lib's central CSS, not in the
// SFC) — the widget bundle loads standalone on the Dashboard, without main.js.
import '@conduction/nextcloud-vue/css/index.css'

OCA.Dashboard.register('zaakAfhandelApp_contactmomenten_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })

	Vue.directive('tooltip', Tooltip)

	const View = Vue.extend(ContactMomentenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
