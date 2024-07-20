<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addKlant'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Klant toevoegen</h2>
			<NcNoteCard v-if="succes" type="success">
				<p>Bijlage succesvol toegevoegd</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div class="form-group">
				<NcTextField :disabled="loading"
					label="Voornaam"
					maxlength="255"
					:value.sync="klant.voornaam" />

				<NcTextField :disabled="loading"
					label="Tussenvoegsel"
					maxlength="255"
					:value.sync="klant.tussenvoegsel" />

				<NcTextField :disabled="loading"
					label="Achternaam"
					maxlength="255"
					:value.sync="klant.achternaam" />

				<NcTextField :disabled="loading"
					label="Telefoonnummer"
					maxlength="255"
					:value.sync="klant.telefoonnummer" />

				<NcTextField :disabled="loading"
					label="Email adres"
					maxlength="255"
					:value.sync="klant.emailadres" />

				<NcTextField :disabled="loading"
					label="Functie"
					maxlength="255"
					:value.sync="klant.functie" />
				<NcTextField :disabled="loading"
					label="Aanmaak kanaal"
					maxlength="255"
					:value.sync="klant.aanmaakkanaal" />

				<NcTextField :disabled="loading"
					label="Bron organisatie"
					maxlength="255"
					:value.sync="klant.bronorganisatie" />

				<NcTextField :disabled="loading"
					label="Bedrijfsnaam"
					maxlength="255"
					:value.sync="klant.bedrijfsnaam" />

				<NcTextField :disabled="loading"
					label="Website Url"
					maxlength="255"
					:value.sync="klant.websiteUrl" />

				<NcTextField :disabled="loading"
					label="Url"
					maxlength="255"
					:value.sync="klant.url" />

				<NcTextField :disabled="loading"
					label="Geverifieerd"
					maxlength="255"
					:value.sync="klant.geverifieerd" />

				<NcTextField :disabled="loading"
					label="Subject"
					maxlength="255"
					:value.sync="klant.subject" />

				<NcTextField :disabled="loading"
					label="Subject Identificatie"
					maxlength="255"
					:value.sync="klant.subjectIdentificatie" />

				<NcTextField :disabled="loading"
					label="Subject Type"
					maxlength="255"
					:value.sync="klant.subjectType" />
			</div>

			<NcButton
				v-if="!succes"
				:disabled="!store.attachmentItem.title || loading"
				type="primary"
				@click="addAttachment()">
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
import {
	NcButton,
	NcModal,
	NcTextField,
} from '@nextcloud/vue'

export default {
	name: 'AddKlant',
	components: {
		NcModal,
		NcTextField,
		NcButton,
	},
	data() {
		return {
			succes: false,
			loading: false,
			error: false,
		}
	},
	updated() {
		if (store.modal === 'addKlant' && !this.hasUpdated) {
			this.hasUpdated = true
		}
	},
	methods: {
		addKlant() {
			this.klantLoading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/klanten',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(this.klant),
				},
			)
				.then((response) => {
					this.succesMessage = true
					this.publicationLoading = false
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					this.publicationLoading = false
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

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
