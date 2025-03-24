<script setup>
import { navigationStore, rolStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!rolStore.rolItem && loading" :size="64" />
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div v-if="rolStore.rolItem">
				<div class="head">
					<h1 class="h1">
						{{ rolStore.rolItem?.url ?? rolStore.rolItem?.rolType }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>

						<NcActionButton @click="navigationStore.setModal('rolForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('deleteRol')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Delete
						</NcActionButton>
					</NcActions>
				</div>

				<div class="detailGrid">
					<div>
						<h4>Omschrijving:</h4>
						<span>{{ rolStore.rolItem?.omschrijving }}</span>
						<h4>Omschrijving generiek:</h4>
						<span>{{ rolStore.rolItem?.omschrijvingGeneriek }}</span>
						<h4>Roltoelichting:</h4>
						<span>{{ rolStore.rolItem?.roltoelichting }}</span>
						<h4>Registratiedatum:</h4>
						<span>{{ rolStore.rolItem?.registratiedatum && new Date(rolStore.rolItem?.registratiedatum).toLocaleString() }}</span>
						<h4>Betrokkene type:</h4>
						<span>{{ rolStore.rolItem?.betrokkeneType }}</span>
						<h4>Naam:</h4>
						<span>
							{{
								(rolStore.rolItem?.betrokkeneIdentificatie?.voornamen || "") +
									(rolStore.rolItem?.betrokkeneIdentificatie?.voorletters ? " " + rolStore.rolItem?.betrokkeneIdentificatie?.voorletters : "") +
									(rolStore.rolItem?.betrokkeneIdentificatie?.geslachtsnaam ? " " + rolStore.rolItem?.betrokkeneIdentificatie?.geslachtsnaam : "") ||
									"Geen naam beschikbaar"
							}}
						</span>

						<h4>BSN:</h4>
						<span>{{ rolStore.rolItem?.betrokkeneIdentificatie?.inpBsn }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { NcActions, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'

export default {
	name: 'RolDetails',
	components: {
		NcActions,
		NcActionButton,
		NcLoadingIcon,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			rol: [],
			loading: false,
		}
	},
	watch: {
		id(newId) {
			this.fetchData(newId)
		},
	},
	// First time the is no emit so lets grap it directly
	mounted() {
		this.fetchData(this.id)
	},
	methods: {
		fetchData(id) {
			this.loading = true

			Promise.all([
				rolStore.getRol(id, { setItem: true }),
			]).finally(() => {
				this.loading = false
			})
		},
	},
}
</script>

<style>
h4 {
  font-weight: bold
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

.grid {
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 1fr 1fr;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  gap: 25px;
}

</style>
