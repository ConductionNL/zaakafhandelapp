<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="store.klantItem" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ store.klantItem.voornaam }} {{ store.klantItem.voorvoegsel }} {{ store.klantItem.achternaam }}
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
				<span> {{ store.klantItem.subject }} </span>

				<div class="detailGrid">
					<div class="gridContent gridFullWidth">
						<b>Klantnummer:</b>
						<p>{{ store.klantItem.klantnummer }}</p>
					</div>

					<div class="gridContent">
						<b>Telefoonnummer:</b>
						<p>{{ store.klantItem.telefoonnummer }}</p>
					</div>
					<div class="gridContent">
						<b>Email adres:</b>
						<p>{{ store.klantItem.emailadres }}</p>
					</div>
					<div class="gridContent">
						<b>Adres:</b>
						<p>{{ store.klantItem.adres }}</p>
					</div>
					<div class="gridContent">
						<b>Functie:</b>
						<p>{{ store.klantItem.functie }}</p>
					</div>
					<div class="gridContent">
						<b>Bedrijfsnaam:</b>
						<p>{{ store.klantItem.bedrijfsnaam }}</p>
					</div>
					<div class="gridContent">
						<b>Website url:</b>
						<p>{{ store.klantItem.websiteUrl }}</p>
					</div>
					<div class="gridContent">
						<b>url:</b>
						<p>{{ store.klantItem.url }}</p>
					</div>
					<div class="gridContent">
						<b>Bron organisatie:</b>
						<p>{{ store.klantItem.bronorganisatie }}</p>
					</div>
					<div class="gridContent">
						<b>Aanmaakkanaal:</b>
						<p>{{ store.klantItem.aanmaakkanaal }}</p>
					</div>
					<div class="gridContent">
						<b>Geverifieerd:</b>
						<p>{{ store.klantItem.geverifieerd }}</p>
					</div>
					<div class="gridContent">
						<b>Subject Identificatie:</b>
						<p>{{ store.klantItem.subjectIdentificatie }}</p>
					</div>
					<div class="gridContent">
						<b>Subject Type:</b>
						<p>{{ store.klantItem.subjectType }}</p>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="!store.klantItem"
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
