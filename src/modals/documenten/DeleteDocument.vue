<script setup>
import { documentStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog name="Document verwijderen"
		size="normal"
		:can-close="false">
		<p v-if="success === null">
			Weet u zeker dat u
			<b>{{ documentStore.documentItem?.titel ?? documentStore.documentItem?.id }}</b>
			permanent wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.
		</p>

		<div v-if="success !== null">
			<NcNoteCard v-if="success" type="success">
				<p>Document succesvol verwijderd</p>
			</NcNoteCard>
			<NcNoteCard v-if="!success && !error" type="error">
				<p>Er is een fout opgetreden bij het verwijderen van het document</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>
		</div>

		<template #actions>
			<NcButton @click="closeDialog">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ success === null ? 'Annuleren' : 'Sluiten' }}
			</NcButton>
			<NcButton v-if="success === null"
				:disabled="loading"
				type="error"
				@click="deleteDocument()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<TrashCanOutline v-if="!loading" :size="20" />
				</template>
				Verwijderen
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
	name: 'DeleteDocument',
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
			success: null,
			loading: false,
			error: null,
			closeModalTimeout: null,
		}
	},
	methods: {
		closeDialog() {
			navigationStore.setModal(null)
			documentStore.zaakId = null
			clearTimeout(this.closeModalTimeout)
		},
		async deleteDocument() {
			this.loading = true

			documentStore.deleteDocument(documentStore.documentItem.id)
				.then(({ response }) => {
					this.success = response.ok
					response.ok && (this.closeModalTimeout = setTimeout(this.closeDialog, 2000))
				}).catch((error) => {
					this.success = false
					this.error = error.message || 'Er is een fout opgetreden bij het verwijderen van het document'
				}).finally(() => {
					this.loading = false
				})
		},
	},
}
</script>
