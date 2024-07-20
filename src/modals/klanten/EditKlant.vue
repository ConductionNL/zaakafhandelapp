<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editKlant'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Klant aanpassen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div class="form-group">
				<NcTextField
					:disabled="loading"
					:value.sync="klant.voornaam"
					label="Voornaam"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.voorvoegsel"
					label="Tussenvoegsels"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.achternaam"
					label="Achternaam"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.telefoonnummer"
					label="Telefoonnummer"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.emailadres"
					label="Email adres"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.functie"
					label="Functie"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.aanmaakkanaal"
					label="Aanmaak kanaal"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.bronorganisatie"
					label="Bron organisatie"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.bedrijfsnaam"
					label="Bedrijfsnaam"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.websiteUrl"
					label="Website Url"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.url"
					label="Url"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.geverifieerd"
					label="Geverifieerd"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.subject"
					label="Subject"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.subjectIdentificatie"
					label="Subject Identificatie"
					maxlength="255" />

				<NcTextField
					:disabled="loading"
					:value.sync="klant.subjectType"
					label="Subject Type"
					maxlength="255" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.attachmentItem.title || loading"
				type="primary"
				@click="addAttachment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcModal,
	NcTextField,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'EditKlant',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'editKlant' && this.hasUpdated) {
			if (this.klant === store.klantItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editKlant' && !this.hasUpdated) {
			// uncomment when api works
			// this.fetchData(store.klantId)
			this.hasUpdated = true
			this.klant = store.klantItem
		}
	},
	methods: {
		fetchData(id) {
			this.klantLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/klanten/${id}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klant = data
						this.klant.data = JSON.stringify(data.data)
						this.catalogi.value = [data.catalogi]
						this.metaData.value = [data.metaData]
					})
					this.klantLoading = false
				})
				.catch((err) => {
					console.error(err)
					this.klantLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		editKlant() {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/klanten/${this.klant.id}`,
				{
					method: 'PUT',
					body: JSON.stringify(this.klant),
				},
			)
				.then((response) => {
					this.succesMessage = true
					this.loading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.loading = false
					console.error(err)
				})
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

/* .modal__content > button {
    margin-block: 6px;
} */

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}

/* .input-field__label {
    margin-block: -6px;
}
.input-field__input:focus + .input-field__label {
    margin-block: 0px;
} */
</style>
