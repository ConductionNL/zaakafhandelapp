<script setup>
import { zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="zakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen open zaken">
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
			Zaak aanmaken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

export default {
	name: 'ZakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
		Folder,
	},

	data() {
		return {
			loading: false,
			zaakItems: [],
		}
	},

	computed: {
		items() {
			return this.zaakItems
		},
	},

	mounted() {
		this.fetchZaakItems()
	},

	methods: {
		fetchZaakItems() {
			this.loading = true
			zaakStore.refreshZakenList()
				.then(() => {
					this.zaakItems = zaakStore.zakenList.map(zaak => ({
						id: zaak.id,
						mainText: zaak.identificatie,
						subText: zaak.zaaktype,
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
.zakenContainer{
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
<style>
:root {
	--zaa-margin-10: 10px;
	--zaa-margin-20: 20px;
	--zaa-margin-50: 50px;
  }
</style>
