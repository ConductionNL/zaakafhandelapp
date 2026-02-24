import Vue from 'vue'
import OrganisatiesWidget from './views/widgets/OrganisatiesWidget.vue'

OCA.Dashboard.register('zaakAfhandelApp_organisaties_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })
	const View = Vue.extend(OrganisatiesWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
