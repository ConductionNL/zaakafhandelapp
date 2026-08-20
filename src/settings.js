import { translatePlural as n, translate as t } from '@nextcloud/l10n'
import { createApp } from 'vue'
import Settings from './views/settings/Settings.vue'

// Library CSS — the settings bundle loads standalone, without main.js.
import '@conduction/nextcloud-vue/css/index.css'

const app = createApp(Settings)

// Vue 3 has no global `Vue.mixin` — it is per-app-instance.
app.mixin({ methods: { t, n } })

app.mount('#zaakafhandelapp-settings')
