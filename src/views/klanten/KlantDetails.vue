<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<h1 class="h1">
					{{ klant.voornaam }} {{ klant.voorvoegsel }} {{ klant.achternaam }}
				</h1>
				<span> {{ klant.subject }} </span>

				<div class="grid">
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
					<div class="gridContent gridFullWidth">
						<h5>Functie:</h5>
						<p>{{ klant.functie }}</p>
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
import { NcLoadingIcon } from '@nextcloud/vue'

export default {
	name: 'KlantDetails',
	components: {
		NcLoadingIcon,
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
  grid-template-columns: repeat( auto-fit, minmax(300px, 1fr) ) !important;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  flex-direction: column;
  gap: 2px !important;
}

.gridFullWidth {
    grid-column: 1 / -1;
}

.tabContainer>* ul>li {
  display: flex;
  flex: 1;
}

.tabContainer>* ul>li:hover {
  background-color: var(--color-background-hover);
}

.tabContainer>* ul>li>a {
  flex: 1;
  text-align: center;
}

.tabContainer>* ul>li>.active {
  background: transparent !important;
  color: var(--color-main-text) !important;
  border-bottom: var(--default-grid-baseline) solid var(--color-primary-element) !important;
}

.tabContainer>* ul {
  display: flex;
  margin: 10px 8px 0 8px;
  justify-content: space-between;
  border-bottom: 1px solid var(--color-border);
}

.tabPanel {
  padding: 20px 10px;
  min-height: 100%;
  max-height: 100%;
  height: 100%;
  overflow: auto;
}
</style>
