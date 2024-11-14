import Vue from 'vue'
import ContactMomentenWidget from './views/widgets/ContactMomentenWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_contactmomenten_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(ContactMomentenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
