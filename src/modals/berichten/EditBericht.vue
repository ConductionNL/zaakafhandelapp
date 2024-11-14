<script setup>
import { berichtStore, navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'editBericht'"
		name="Bericht"
		size="normal"
		@closing="closeModalFromButton()">
		<NcNoteCard v-if="success" type="success">
			<p>Bericht succesvol aangepast</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.title"
				label="Title" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.onderwerp"
				label="Onderwerp" />

			<NcTextArea
				:disabled="loading"
				:value.sync="berichtItem.berichttekst"
				label="Berichttekst" />

			<NcTextArea
				:disabled="loading"
				:value.sync="berichtItem.inhoud"
				label="Inhoud (base64)" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.bijlageType"
				label="Bijlage type" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.soortGebruiker"
				label="Soort gebruiker" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.publicatieDatum"
				label="Publicatiedatum" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.aanmaakDatum"
				label="Aanmaak datum" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.berichtType"
				label="Bericht type" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.referentie"
				label="Referentie" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.berichtID"
				label="Bericht ID" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.batchID"
				label="Batch ID" />

			<NcTextField
				:disabled="true"
				:value="klantStore.klantItem?.id || berichtItem.gebruikerID"
				label="Gebruiker ID" />

			<NcTextField
				:disabled="loading"
				:value.sync="berichtItem.onderwerp"
				label="Volgorde" />
		</div>

		<template #actions>
			<NcButton @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success ? 'Sluiten' : 'Annuleer' }}
			</NcButton>
			<NcButton @click="openLink('https://conduction.gitbook.io/opencatalogi-nextcloud/gebruikers/publicaties', '_blank')">
				<template #icon>
					<Help :size="20" />
				</template>
				Help
			</NcButton>
			<NcButton v-if="!success"
				:disabled="loading"
				type="primary"
				@click="editBericht()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && berichtStore.berichtItem?.id" :size="20" />
					<Plus v-if="!loading && !berichtStore.berichtItem?.id" :size="20" />
				</template>
				{{ berichtStore.berichtItem?.id ? 'Opslaan' : 'Aanmaken' }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcLoadingIcon, NcDialog, NcTextField, NcTextArea, NcNoteCard } from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Help from 'vue-material-design-icons/Help.vue'

export default {
	name: 'EditBericht',
	components: {
		NcDialog,
		NcTextField,
		NcTextArea,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		// Icons
		ContentSaveOutline,
		Cancel,
		Plus,
		Help,
	},
	props: {
		dashboardWidget: {
			type: Boolean,
			required: false,
		},
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
			hasUpdated: false,
			berichtItem: {
				title: '',
				onderwerp: '',
				berichttekst: '',
				inhoud: '',
				bijlageType: '',
				soortGebruiker: '',
				publicatieDatum: '',
				aanmaakDatum: '',
				berichtType: '',
				referentie: '',
				berichtID: '',
				batchID: '',
				gebruikerID: '',
				volgorde: '',
			},
		}
	},
	updated() {
		if (navigationStore.modal === 'editBericht' && !this.hasUpdated) {
			if (berichtStore.berichtItem?.id) {
				this.berichtItem = {
					...berichtStore.berichtItem,
					title: berichtStore.berichtItem.title || '',
					onderwerp: berichtStore.berichtItem.onderwerp || '',
					berichttekst: berichtStore.berichtItem.berichttekst || '',
					inhoud: berichtStore.berichtItem.inhoud || '',
					bijlageType: berichtStore.berichtItem.bijlageType || '',
					soortGebruiker: berichtStore.berichtItem.soortGebruiker || '',
					publicatieDatum: berichtStore.berichtItem.publicatieDatum || '',
					aanmaakDatum: berichtStore.berichtItem.aanmaakDatum || '',
					berichtType: berichtStore.berichtItem.berichtType || '',
					referentie: berichtStore.berichtItem.referentie || '',
					berichtID: berichtStore.berichtItem.berichtID || '',
					batchID: berichtStore.berichtItem.batchID || '',
					gebruikerID: klantStore.klantItem?.id || berichtStore.berichtItem.gebruikerID || '',
					volgorde: berichtStore.berichtItem.volgorde || '',
				}
			} else if (klantStore.klantItem?.id) {
				this.berichtItem.gebruikerID = klantStore.klantItem.id
			}
			this.hasUpdated = true
		}
	},
	methods: {
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		closeModal() {
			navigationStore.setModal(false)
			this.success = false
			this.loading = false
			this.error = false
			this.hasUpdated = false
			this.berichtItem = {
				title: '',
				onderwerp: '',
				berichttekst: '',
				inhoud: '',
				bijlageType: '',
				soortGebruiker: '',
				publicatieDatum: '',
				aanmaakDatum: '',
				berichtType: '',
				referentie: '',
				berichtID: '',
				batchID: '',
				gebruikerID: '',
				volgorde: '',
			}
		},
		async editBericht() {
			this.loading = true
			try {
				await berichtStore.saveBericht({
					...this.berichtItem,
					gebruikerID: klantStore.klantItem?.id || this.berichtItem.gebruikerID,
				})
				this.success = true
				this.loading = false
				setTimeout(this.closeModal, 2000)
				if (this.dashboardWidget === true) {
					this.$emit('save-success')
				}

			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while saving the bericht'
			}
		},
		openLink(url, target) {
			window.open(url, target)
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

.berichtDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
