<script setup>
import { navigationStore, taakStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<TakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!taakStore.taakItem?.id || navigationStore.selected != 'taken' "
				class="detailContainer"
				name="Geen taak"
				description="Nog geen taak geselecteerd">
				<template #icon>
					<CalendarMonthOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editTaak')">
						Taak toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<TaakDetails v-if="taakStore.taakItem?.id && navigationStore.selected === 'taken'" :taak-id="taakStore.taakItem?.id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import TakenList from './TakenList.vue'
import TaakDetails from './TaakDetails.vue'
// eslint-disable-next-line n/no-missing-import
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'

export default {
	name: 'TakenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		TakenList,
		TaakDetails,
		CalendarMonthOutline,
	},
}
</script>
