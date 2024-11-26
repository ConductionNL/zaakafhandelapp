<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder div for all of the vieuws-->
	<NcAppContent>
		<template #default>
			<BerichtenIndex v-if="navigationStore.selected === 'berichten'" />
			<BesluitenIndex v-if="navigationStore.selected === 'besluiten'" />
			<DashboardIndex v-if="navigationStore.selected === 'dashboard'" />
			<DocumentenIndex v-if="navigationStore.selected === 'documenten'" />
			<KlantenIndex v-if="navigationStore.selected === 'klanten'" />
			<ResultatenIndex v-if="navigationStore.selected === 'resultaten'" />
			<RollenIndex v-if="navigationStore.selected === 'rollen'" />
			<StatusssenIndex v-if="navigationStore.selected === 'statusssen'" />
			<TakenIndex v-if="navigationStore.selected === 'taken'" />
			<ZaakTypenIndex v-if="navigationStore.selected === 'zaakTypen'" />
			<ZakenIndex v-if="navigationStore.selected === 'zaken'" />
			<ContactMomentenIndex v-if="navigationStore.selected === 'contactMomenten'" />
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
import ContactMomentenIndex from './contactMomenten/ContactMomentenIndex.vue'
export default {
	name: 'Views',
	components: {
		BerichtenIndex,
		BesluitenIndex,
		DashboardIndex,
		SearchIndex,
		ContactMomentenIndex,
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
