import Vue from 'vue'
import { PiniaVuePlugin } from 'pinia'
import pinia from './pinia.js'
import App from './App.vue'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'

Vue.mixin({ methods: { t, n } })

Vue.use(PiniaVuePlugin)

Vue.directive('tooltip', Tooltip)

new Vue(
	{
		pinia,
		render: h => h(App),
	},
).$mount('#content')
