<script setup>
import { contactMomentStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog
		name="Verwijder contact moment"
		size="normal"
		:can-close="false">
		<p v-if="!success">
			Wil je <b>{{ contactMomentStore.contactMomentItem.titel }}</b> verwijderen? Deze actie is niet ongedaan te maken.
		</p>

		<NcNoteCard v-if="success" type="success">
			<p>Contact moment succesvol verwijderd</p>
		</NcNoteCard>
		<NcNoteCard v-if="error" type="error">
			<p>{{ error }}</p>
		</NcNoteCard>

		<template #actions>
			<NcButton
				@click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success ? 'Sluiten' : 'Annuleren' }}
			</NcButton>
			<NcButton
				v-if="!success"
				:disabled="loading"
				type="error"
				@click="deleteContactMoment()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<TrashCanOutline v-if="!loading" :size="20" />
				</template>
				Verwijder
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import {
	NcButton,
	NcDialog,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'DeleteContactMoment',
	components: {
		NcDialog,
		NcButton,
		NcLoadingIcon,
		NcNoteCard,
		// Icons
		TrashCanOutline,
		Cancel,
	},
	data() {
		return {
			success: false,
			loading: false,
			error: false,
			closeTimeoutFunc: null,
		}
	},
	methods: {
		closeModal() {
			navigationStore.setModal(false)
			clearTimeout(this.closeTimeoutFunc)
			this.success = null
		},
		async deleteContactMoment() {
			this.loading = true
			try {
				await contactMomentStore.deleteContactMoment(contactMomentStore.contactMomentItem)
				// Close modal or show success message
				this.success = true
				this.loading = false
				this.error = false

				contactMomentStore.setContactMomentItem(null)
				this.closeTimeoutFunc = setTimeout(this.closeModal, 2000)
			} catch (error) {
				this.loading = false
				this.success = false
				this.error = error.message || 'An error occurred while deleting the contact moment'
			}
		},
	},
}
</script>
