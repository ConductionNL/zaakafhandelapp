import Vue from 'vue'
import OpenZakenWidget from './views/widgets/OpenZakenWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_openzaak_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(OpenZakenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
