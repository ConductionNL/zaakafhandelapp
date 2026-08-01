<script setup>
import { translate as t } from '@nextcloud/l10n'
import { berichtStore, navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog v-if="navigationStore.modal === 'editBericht'"
		:name="t('zaakafhandelapp', 'Message')"
		size="normal"
		@closing="closeModalFromButton()">
		<NcNoteCard v-if="success" type="success">
			<p>{{ t('zaakafhandelapp', 'Message successfully updated') }}</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<div v-if="!success" class="form-group">
			<NcTextField
				v-model="berichtItem.title"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Title')" />

			<NcTextField
				v-model="berichtItem.onderwerp"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Subject')" />

			<NcTextArea
				v-model="berichtItem.berichttekst"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Message text')" />

			<NcTextArea
				v-model="berichtItem.inhoud"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Content (base64)')" />

			<NcTextField
				v-model="berichtItem.bijlageType"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Attachment type')" />

			<NcTextField
				v-model="berichtItem.soortGebruiker"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'User type')" />

			<NcTextField
				v-model="berichtItem.publicatieDatum"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Publication date')" />

			<NcTextField
				v-model="berichtItem.aanmaakDatum"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Creation date')" />

			<NcTextField
				v-model="berichtItem.berichtType"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Message type')" />

			<NcTextField
				v-model="berichtItem.referentie"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Reference')" />

			<NcTextField
				v-model="berichtItem.berichtID"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Message ID')" />

			<NcTextField
				v-model="berichtItem.batchID"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Batch ID')" />

			<NcTextField
				:disabled="true"
				:model-value="klantStore.klantItem?.id || berichtItem.gebruikerID"
				:label="t('zaakafhandelapp', 'User ID')" />

			<NcTextField
				v-model="berichtItem.onderwerp"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Order')" />
		</div>

		<template #actions>
			<NcButton @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success ? t('zaakafhandelapp', 'Close') : t('zaakafhandelapp', 'Cancel') }}
			</NcButton>
			<NcButton @click="openLink('https://conduction.gitbook.io/opencatalogi-nextcloud/gebruikers/publicaties', '_blank')">
				<template #icon>
					<Help :size="20" />
				</template>
				Help
			</NcButton>
			<NcButton v-if="!success"
				:disabled="loading"
				variant="primary"
				@click="editBericht()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading && berichtStore.berichtItem?.id" :size="20" />
					<Plus v-if="!loading && !berichtStore.berichtItem?.id" :size="20" />
				</template>
				{{ berichtStore.berichtItem?.id ? t('zaakafhandelapp', 'Save') : t('zaakafhandelapp', 'Create') }}
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
	/**
	 * @spec openspec/specs/ui-modals/spec.md#REQ-003
	 */
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
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModalFromButton() {
			setTimeout(() => {
				this.closeModal()
			}, 300)
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
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
			this.$emit('close-modal')

		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-003
		 */
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
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-004
		 */
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
