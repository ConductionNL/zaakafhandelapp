<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<TakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.taakId || store.selected != 'taken' "
				class="detailContainer"
				name="Geen taak"
				description="Nog geen taak geselecteerd">
				<template #icon>
					<CalendarMonthOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('addTaak')">
						Taak toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<TaakDetails v-if="store.taakId && store.selected === 'taken'" :taak-id="store.taakId" />
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
	data() {
		return {
			takenId: undefined,
		}
	},
}
</script>
