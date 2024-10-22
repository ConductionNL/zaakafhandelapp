import Vue from 'vue'
import Settings from './views/settings/Settings.vue'

Vue.mixin({ methods: { t, n } })

new Vue(
	{
		render: h => h(Settings),
	},
).$mount('#settings')
