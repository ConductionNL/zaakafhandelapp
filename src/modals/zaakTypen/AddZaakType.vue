<script setup>
import { zaakTypeStore, navigationStore } from '../../store.js'
</script>

<template>
	<NcModal v-if="navigationStore.modal === 'addZaakType'" ref="modalRef" @close="store.setModal(false)">
		<div class="modalContent">
			<h2>Zaaktype toevoegen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Zaaktype succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div v-if="!succes" class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.identificatie"
					label="Identificatie"
					maxlength="255"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.omschrijving"
					label="Omschrijving"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.omschrijvingGeneriek"
					label="omschrijvingGeneriek"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.vertrouwelijkheidaanduiding"
					label="vertrouwelijkheidaanduiding"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.doel"
					label="doel"
					maxlength="9"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.aanleiding"
					label="aanleiding"
					required />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.toelichting"
					label="toelichting"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.indicatieInternOfExtern"
					label="indicatieInternOfExtern" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.handelingInitiator"
					label="handelingInitiator" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.onderwerp"
					label="onderwerp" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.handelingBehandelaar"
					label="handelingBehandelaar" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.doorlooptijd"
					label="doorlooptijd" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.servicenorm"
					label="servicenorm" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.opschortingEnAanhoudingMogelijk"
					label="opschortingEnAanhoudingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.verlengingMogelijk"
					label="verlengingMogelijk" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.verlengingstermijn"
					label="trefwoorden" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.publicatieIndicatie"
					label="publicatieIndicatie" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.publicatietekst"
					label="publicatietekst" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.productenOfDiensten"
					label="productenOfDiensten" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.selectielijstProcestype"
					label="selectielijstProcestype" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.referentieproces"
					label="Referentieprocesnaam" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.catalogus"
					label="catalogus" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.beginGeldigheid"
					label="beginGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.eindeGeldigheid"
					label="eindeGeldigheid" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.beginObject"
					label="beginObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.eindeObject"
					label="eindeObject" />

				<NcTextField
					:disabled="loading"
					:value.sync="zaakTypeStore.zaakTypeItem.versiedatum"
					label="versiedatum" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.zaakTypeItem.omschrijving || loading"
				type="primary"
				@click="addZaakType()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<Plus v-if="!loading" :size="20" />
				</template>
				Toevoegen
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcSelect, NcTextArea, NcLoadingIcon, NcNoteCard } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AddZaakType',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
		NcTextArea,
		NcLoadingIcon,
		NcNoteCard,
		// Icons
		Plus,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
			// Select options
			zaakTypeLoading: false,
			zaaktype: false,
		}
	},
	mounted() {
		// Lets create an empty zaak type item
		store.setZaakTypeItem([])
	},
	methods: {
		addZaakType() {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(store.zaakTypeItem),
				},
			)
				.then((response) => {
					this.succes = true
					this.loading = false
					// Wait for the user to read the feedback then close the model
					const self = this
					setTimeout(function() {
						self.succes = false
						store.setModal(false)
					}, 2000)
				})
				.catch((err) => {
					this.loading = false
					this.error = err
					console.error(err)
				})
		},
	},
}
</script>

<style>
.modalContent {
    margin: var(--zaa-margin-50);
    text-align: center;
}

</style>
