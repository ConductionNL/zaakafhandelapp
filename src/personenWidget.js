import Vue from 'vue'
import PersonenWidget from './views/widgets/PersonenWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_personen_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(PersonenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
