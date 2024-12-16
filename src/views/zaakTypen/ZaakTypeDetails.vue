<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<div class="head">
				<h1 class="h1">
					{{ zaakTypeStore.zaakTypeItem?.identificatie }}
				</h1>

				<NcActions :primary="true" menu-name="Acties">
					<template #icon>
						<DotsHorizontal :size="20" />
					</template>
					<NcActionButton @click="navigationStore.setModal('zaaktypeForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<NcActionButton disabled>
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen
					</NcActionButton>
					<!-- Add more action buttons as needed -->
				</NcActions>
			</div>

			<div class="detailGrid">
				<div>
					<h4>Omschrijving:</h4>
					<span>{{ zaakTypeStore.zaakTypeItem?.omschrijving || '-' }}</span>
				</div>
				<div>
					<h4>Generieke omschrijving:</h4>
					<span>{{ zaakTypeStore.zaakTypeItem?.omschrijvingGeneriek || '-' }}</span>
				</div>
				<div>
					<h4>Vertrouwelijkheidaanduiding:</h4>
					<span>{{ zaakTypeStore.zaakTypeItem?.vertrouwelijkheidaanduiding || '-' }}</span>
				</div>
				<div>
					<h4>Doel:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.doel || '-' }}</p>
				</div>
				<div>
					<h4>Aanleiding:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.aanleiding || '-' }}</p>
				</div>
				<div>
					<h4>Toelichting:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.toelichting || '-' }}</p>
				</div>
				<div>
					<h4>Indicatie intern of extern:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.indicatieInternOfExtern || '-' }}</p>
				</div>
				<div>
					<h4>Handeling initiatie:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.handelingInitiator || '-' }}</p>
				</div>
				<div>
					<h4>Onderwerp:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.onderwerp || '-' }}</p>
				</div>
				<div>
					<h4>Handeling behandelaar:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.handelingBehandelaar || '-' }}</p>
				</div>
				<div>
					<h4>Doorlooptijd:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.doorlooptijd || '-' }}</p>
				</div>
				<div>
					<h4>Servicenorm:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.servicenorm || '-' }}</p>
				</div>
				<div>
					<h4>Opschorting en aanhouding mogelijk:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.opschortingEnAanhoudingMogelijk || '-' }}</p>
				</div>
				<div>
					<h4>Verlenging mogelijk:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.verlengingMogelijk || '-' }}</p>
				</div>
				<div>
					<h4>Verlengingstermijn:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.verlengingstermijn || '-' }}</p>
				</div>
				<div>
					<h4>Publicatie indicatie:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.publicatieIndicatie || '-' }}</p>
				</div>
				<div>
					<h4>Publicatietekst:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.publicatietekst || '-' }}</p>
				</div>
				<div>
					<h4>Producten of diensten:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.productenOfDiensten || '-' }}</p>
				</div>
				<div>
					<h4>Selectielijst proces type:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.selectielijstProcestype || '-' }}</p>
				</div>
				<div>
					<h4>Referentieproces:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.referentieproces || '-' }}</p>
				</div>
				<div>
					<h4>Catalogus:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.catalogus || '-' }}</p>
				</div>
				<div>
					<h4>Begin geldigheid:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.beginGeldigheid || '-' }}</p>
				</div>
				<div>
					<h4>Einde geldigheid:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.eindeGeldigheid || '-' }}</p>
				</div>
				<div>
					<h4>Begin object:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.beginObject || '-' }}</p>
				</div>
				<div>
					<h4>Einde object:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.eindeObject || '-' }}</p>
				</div>
				<div>
					<h4>Versiedatum:</h4>
					<p>{{ zaakTypeStore.zaakTypeItem?.versiedatum || '-' }}</p>
				</div>
			</div>
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
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

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
			currentId: null,
		}
	},
	mounted() {
		this.fetchData()
	},
	updated() {
		if (zaakTypeStore.zaakTypeItem?.id && this.currentId !== zaakTypeStore.zaakTypeItem.id) {
			this.currentId = zaakTypeStore.zaakTypeItem.id
			this.fetchData()
		}
	},
	methods: {
		fetchData() {
			this.loading = true

			// get current zaaktype once
			zaakTypeStore.getZaakType(zaakTypeStore.zaakTypeItem.id, { setItem: true })
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
	grid-gap: 0.5rem;
	grid-template-columns: 1fr 1fr;
}
.detailGrid h4 {
    margin: 0 !important;
    font-size: initial !important;
}
</style>
