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
						{{ klant.voornaam }} {{ klant.voorvoegsel }} {{ klant.achternaam }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="editKlant(klant)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</NcActions>
				</div>
				<span> {{ klant.subject }} </span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<h5>Klantnummer:</h5>
						<p>{{ klant.klantnummer }}</p>
					</div>

					<div class="gridContent">
						<h5>Telefoonnummer:</h5>
						<p>{{ klant.telefoonnummer }}</p>
					</div>
					<div class="gridContent">
						<h5>Email adres:</h5>
						<p>{{ klant.emailadres }}</p>
					</div>
					<div class="gridContent">
						<h5>Adres:</h5>
						<p>{{ klant.adres }}</p>
					</div>
					<div class="gridContent">
						<h5>Functie:</h5>
						<p>{{ klant.functie }}</p>
					</div>
					<div class="gridContent">
						<h5>Bedrijfsnaam:</h5>
						<p>{{ klant.bedrijfsnaam }}</p>
					</div>
					<div class="gridContent">
						<h5>Website url:</h5>
						<p>{{ klant.websiteUrl }}</p>
					</div>
					<div class="gridContent">
						<h5>url:</h5>
						<p>{{ klant.url }}</p>
					</div>
					<div class="gridContent">
						<h5>Bron organisatie:</h5>
						<p>{{ klant.bronorganisatie }}</p>
					</div>
					<div class="gridContent">
						<h5>Aanmaakkanaal:</h5>
						<p>{{ klant.aanmaakkanaal }}</p>
					</div>
					<div class="gridContent">
						<h5>Geverifieerd:</h5>
						<p>{{ klant.geverifieerd }}</p>
					</div>
					<div class="gridContent">
						<h5>Subject Identificatie:</h5>
						<p>{{ klant.subjectIdentificatie }}</p>
					</div>
					<div class="gridContent">
						<h5>Subject Type:</h5>
						<p>{{ klant.subjectType }}</p>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Klant details aan het laden" />
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'KlantDetails',
	components: {
		NcLoadingIcon,
		NcActions,
		NcActionButton,
	},
	props: {
		klantId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			klant: [],
			loading: false,
		}
	},
	watch: {
		klantId: {
			handler(klantId) {
				this.fetchData(klantId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.klantId)
	},
	methods: {
		editKlant(klant) {
			store.setKlantItem(klant)
			store.setModal('editKlant')
		},
		fetchData(klantId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/klanten/' + klantId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klant = data
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
.detailContainer {
    padding: 0.5rem;
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
  grid-gap: 1rem 24px !important;
  grid-template-columns: repeat( auto-fit, minmax(300px, 1fr) ) !important;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  flex-direction: column;
  gap: 2px !important;
}
.gridContent > h5 {
    margin-top: 12px !important;
}

.gridFullWidth {
    grid-column: 1 / -1;
}

</style>
