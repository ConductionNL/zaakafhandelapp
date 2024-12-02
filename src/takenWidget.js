import Vue from 'vue'
import TakenWidget from './views/widgets/TakenWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_taak_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(TakenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
