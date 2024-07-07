<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<TakenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.taakItem || store.selected != 'taken' "
				class="detailContainer"
				name="Geen taak"
				description="Nog geen taak geselecteerd">				
				<template #icon>
					<CalendarMonthOutline/>
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('taakAdd')">
						Taak toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<TaakDetails v-if="store.taakItem && store.selected === 'taken'" :taakId="store.taakItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent,NcButton } from '@nextcloud/vue'
import TakenList from './list.vue'
import TaakDetails from './details.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'

export default {
	name: 'ZakenIndex',
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
	}
}
</script>
