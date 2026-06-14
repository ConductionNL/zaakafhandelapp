import Vue from 'vue'
import ContactMomentenWidget from './views/widgets/ContactMomentenWidget.vue'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'

OCA.Dashboard.register('zaakAfhandelApp_contactmomenten_widget', async (el, { widget }) => {
	Vue.mixin({ methods: { t, n } })

	Vue.directive('tooltip', Tooltip)

	const View = Vue.extend(ContactMomentenWidget)
	new View({
		propsData: { title: widget.title },
	}).$mount(el)
})
