<template>
	<div class="takenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent :title="t('Geen open taken')">
						<template #icon>
							<FolderIcon />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<NcButton type="primary" @click="search">
			<template #icon>
				<Plus :size="20" />
			</template>
			Taak aanmaken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'TakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
	},

	data() {
		return {
			loading: false,
			zaakItems: [
				{
					id: '1',
					mainText: 'Terugbel verzoek brandkraan',
					subText: 'brandkraan staat schuin',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/calendar-month-outline.svg',
				},
			],
		}
	},

	computed: {
		items() {
			return this.zaakItems
		},
	},

	mounted() {
	},

	methods: {
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style>
.takenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
	overflow: auto;
 }
</style>
