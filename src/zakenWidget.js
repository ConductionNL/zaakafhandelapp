import Vue from 'vue'
import ZakenWidget from './views/widgets/ZakenWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_zaak_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(ZakenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
