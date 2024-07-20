<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ taak.title }}
					</h1>
					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="editTaak(taak)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</NcActions>
				</div>
				<div class="grid">
					<div class="gridContent">
						<h4>Sammenvatting:</h4>
						<span>{{ taak.onderwerp }}</span>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Taak details aan het laden" />
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'TaakDetails',
	components: {
		NcLoadingIcon,
	},
	props: {
		taakId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			taak: [],
			loading: false,
		}
	},
	watch: {
		taakId: {
			handler(taakId) {
				this.fetchData(taakId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.taakId)
	},
	methods: {
		fetchData(taakId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/taken/' + taakId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.taak = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		editTaak(taak) {
			store.setTaakItem(taak)
			store.setModal('editTaak')
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
