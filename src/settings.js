import Vue from 'vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'
import Settings from './views/settings/Settings.vue'

Vue.mixin({ methods: { t, n } })

new Vue(
	{
		render: h => h(Settings),
	},
).$mount('#zaakafhandelapp-settings')
