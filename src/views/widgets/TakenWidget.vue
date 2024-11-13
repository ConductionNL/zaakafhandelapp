<script setup>
import { taakStore } from '../../store/store.js'
</script>

<template>
	<div class="takenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen open taken">
						<template #icon>
							<Folder />
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
import Folder from 'vue-material-design-icons/Folder.vue'

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
			taakItems: [],
		}
	},

	computed: {
		items() {
			return this.taakItems
		},
	},

	mounted() {
		this.fetchTaakItems()
	},

	methods: {
		fetchTaakItems() {
			this.loading = true
			taakStore.refreshTakenList()
				.then(() => {
					this.taakItems = taakStore.takenList.map(taak => ({
						id: taak.id,
						mainText: taak.title,
						subText: taak.type,
						avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',
					}))

					this.loading = false
				})
		},
		search() {
			console.info('click')
		},
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style scoped>
.takenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
	overflow: auto;
	margin-block-end: var(--zaa-margin-10);
 }
</style>
