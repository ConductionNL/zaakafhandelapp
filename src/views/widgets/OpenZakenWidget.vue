<script setup>
import { zaakStore } from '../../store/store.js'
</script>

<template>
	<div class="openZakenContainer">
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
				<OpenInApp :size="20" />
			</template>
			Zaken bekijken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

export default {
	name: 'OpenZakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		OpenInApp,
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
.openZakenContainer{
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
