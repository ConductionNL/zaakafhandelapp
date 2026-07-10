import Vue from 'vue'
import TakenWidget from './views/widgets/TakenWidget.vue'

// Library CSS (CnDataTable styles live in the lib's central CSS, not in the
// SFC) — the widget bundle loads standalone on the Dashboard, without main.js.
import '@conduction/nextcloud-vue/css/index.css'

OCA.Dashboard.register('zaakAfhandelApp_taak_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(TakenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
