<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<div class="head">
				<h1 class="h1">
					{{ zaakTypeStore.zaakTypeItem?.name }}
				</h1>

				<NcActions :primary="true" menu-name="Acties">
					<template #icon>
						<DotsHorizontal :size="20" />
					</template>
					<NcActionButton @click="navigationStore.setModal('zaakTypeForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<!-- Add more action buttons as needed -->
				</NcActions>
			</div>

			{{ zaakTypeStore.zaakTypeItem?.summary }}
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Zaaktype details aan het laden" />
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'ZaakTypeDetails',
	components: {
		// Components
		NcLoadingIcon,
		NcActions,
		NcActionButton,
		// Icons
		DotsHorizontal,
		Pencil,
	},
	data() {
		return {
			loading: true,
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData() {
			this.loading = true

			// get current zaaktype once
			zaakTypeStore.getZaakType(zaakTypeStore.zaakTypeItem.uuid, { setZaakTypeItem: true })
				.then(() => {
					this.loading = false
				})
		},
	},
}
</script>

<style scoped>
h4 {
	font-weight: bold;
}

.head {
	display: flex;
	justify-content: space-between;
}

.h1 {
	display: block !important;
	font-size: 2em !important;
	margin-block-start: 0.67em !important;
	margin-block-end: 0.67em !important;
	margin-inline-start: 0px !important;
	margin-inline-end: 0px !important;
	font-weight: bold !important;
	unicode-bidi: isolate !important;
}

.detailGrid {
	display: grid;
	grid-gap: 24px;
	grid-template-columns: 1fr 1fr;
	margin-block-start: var(--zaa-margin-50);
	margin-block-end: var(--zaa-margin-50);
}
</style>
