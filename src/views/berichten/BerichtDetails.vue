<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ bericht.onderwerp }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="editBericht(bericht)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</NcActions>
				</div>
				<div class="detailGrid">
					<div>
						<h4>Berichttekst:</h4>
						<span>{{ bericht.berichttekst }}</span>
					</div>
					<div>
						<h4>Berichttekst:</h4>
						<p>{{ bericht.berichttekst }}</p>
					</div>
					<div>
						<h4>Inhoud:</h4>
						<p>{{ bericht.inhoud }}</p>
					</div>
					<div>
						<h4>Soort gebruiker:</h4>
						<span>{{ bericht.soortGebruiker }}</span>
					</div>
					<div>
						<h4>Publicatiedatum:</h4>
						<span>{{ bericht.publicatieDatum }}</span>
					</div>
					<div>
						<h4>Aanmaak datum:</h4>
						<span>{{ bericht.aanmaakDatum }}</span>
					</div>
					<div>
						<h4>Bericht type:</h4>
						<span>{{ bericht.berichtType }}</span>
					</div>
					<div>
						<h4>Referentie:</h4>
						<span>{{ bericht.referentie }}</span>
					</div>
					<div>
						<h4>Bericht ID:</h4>
						<span>{{ bericht.berichtID }}</span>
					</div>
					<div>
						<h4>Batch ID:</h4>
						<span>{{ bericht.batchID }}</span>
					</div>
					<div>
						<h4>Gebruiker ID:</h4>
						<span>{{ bericht.gebruikerID }}</span>
					</div>
					<div>
						<h4>Volgorde:</h4>
						<span>{{ bericht.volgorde }}</span>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Bericht details aan het laden" />
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'BerichtDetails',
	components: {
		// Components
		NcLoadingIcon,
		NcActions,
		NcActionButton,
		// Icons
		Pencil,
		DotsHorizontal,
	},
	props: {
		berichtId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			bericht: [],
			loading: false,
		}
	},
	watch: {
		berichtId: {
			handler(berichtId) {
				this.fetchData(berichtId)
			},
			deep: true,
		},
	},
	// First time the is no emit so lets grap it directly
	mounted() {
		this.fetchData(store.berichtItem.id)
	},
	methods: {
		editBericht(bericht) {
			store.setBerichtItem(bericht)
			navigationStore.setModal('editBericht')
		},
		fetchData(berichtId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/berichten/' + berichtId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.bericht = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
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

.tabPanel {
  padding: 20px 10px;
  min-height: 100%;
  max-height: 100%;
  height: 100%;
  overflow: auto;
}
</style>
