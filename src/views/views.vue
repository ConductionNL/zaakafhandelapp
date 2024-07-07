<script setup>
import { store } from '../store.js'
</script>

<template>
	<!-- Placeholder div for all of the vieuws-->
	<NcAppContent>
		<template #default>
			<Berichten v-if="store.selected === 'berichten'" />
			<Besluiten v-if="store.selected === 'besluiten'" />
			<Dashboard v-if="store.selected === 'dashboard'" />
			<Documenten v-if="store.selected === 'documenten'" />
			<Klanten v-if="store.selected === 'klanten'" />
			<Resultaten v-if="store.selected === 'resultaten'" />
			<Rollen v-if="store.selected === 'rollen'" />
			<Statusssen v-if="store.selected === 'statusssen'" />
			<Taken v-if="store.selected === 'taken'" />
			<ZaakTypen v-if="store.selected === 'zaakTypen'" />
			<Zaken v-if="store.selected === 'zaken'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import Berichten from './berichten/index.vue'
import Besluiten from './besluiten/index.vue'
import Dashboard from './dashboard/index.vue'
import Documenten from './documenten/index.vue'
import Klanten from './klanten/index.vue'
import Resultaten from './resultaten/index.vue'
import Rollen from './rollen/index.vue'
import Statusssen from './statusssen/index.vue'
import Taken from './taken/index.vue'
import ZaakTypen from './zaakTypen/index.vue'
import Zaken from './zaken/index.vue'

export default {
	name: 'Modals',
	components: {
		Berichten,
		Besluiten,
		Dashboard,
		Documenten,
		Klanten,
		Resultaten,
		Rollen,
		Statusssen,
		Taken,
		ZaakTypen,
		NcAppContent,
	},
}
</script>
