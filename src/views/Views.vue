<script setup>
import { store } from '../store.js'
</script>

<template>
	<!-- Placeholder div for all of the vieuws-->
	<NcAppContent>
		<template #default>
			<BerichtenIndex v-if="store.selected === 'berichten'" />
			<BesluitenIndex v-if="store.selected === 'besluiten'" />
			<DashboardIndex v-if="store.selected === 'dashboard'" />
			<SearchIndex v-if="store.selected === 'search'" />
			<DocumentenIndex v-if="store.selected === 'documenten'" />
			<KlantenIndex v-if="store.selected === 'klanten'" />
			<ResultatenIndex v-if="store.selected === 'resultaten'" />
			<RollenIndex v-if="store.selected === 'rollen'" />
			<StatusssenIndex v-if="store.selected === 'statusssen'" />
			<TakenIndex v-if="store.selected === 'taken'" />
			<ZaakTypenIndex v-if="store.selected === 'zaakTypen'" />
			<ZakenIndex v-if="store.selected === 'zaken'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import BerichtenIndex from './berichten/BerichtenIndex.vue'
import BesluitenIndex from './besluiten/BesluitenIndex.vue'
import DashboardIndex from './dashboard/DashboardIndex.vue'
import SearchIndex from './search/SearchIndex.vue'
import DocumentenIndex from './documenten/DocumentenIndex.vue'
import KlantenIndex from './klanten/KlantenIndex.vue'
import ResultatenIndex from './resultaten/ResultatenIndex.vue'
import RollenIndex from './rollen/RollenIndex.vue'
import StatusssenIndex from './statussen/StatussenIndex.vue'
import TakenIndex from './taken/TakenIndex.vue'
import ZaakTypenIndex from './zaakTypen/ZakenTypenIndex.vue'
import ZakenIndex from './zaken/ZakenIndex.vue'

export default {
	name: 'Views',
	components: {
		BerichtenIndex,
		BesluitenIndex,
		DashboardIndex,
		SearchIndex,
		DocumentenIndex,
		KlantenIndex,
		ResultatenIndex,
		RollenIndex,
		StatusssenIndex,
		TakenIndex,
		ZaakTypenIndex,
		NcAppContent,
	},
}
</script>
