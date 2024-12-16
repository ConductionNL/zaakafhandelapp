<script setup>
import { zaakTypeStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcDialog name="Zaaktype verwijderen"
		size="normal"
		:can-close="false">
		<p v-if="success === null">
			Weet u zeker dat u <b>{{ zaakTypeStore.zaakTypeItem?.identificatie }}</b> permanent wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.
		</p>

		<div v-if="success !== null">
			<NcNoteCard v-if="success" type="success">
				<p>Zaaktype succesvol verwijderd</p>
			</NcNoteCard>
			<NcNoteCard v-if="!success && !error" type="error">
				<p>Er is een fout opgetreden bij het verwijderen van het zaaktype</p>
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
				@click="deleteZaakType()">
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
	name: 'DeleteZaaktype',
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
			clearTimeout(this.closeModalTimeout)
		},
		async deleteZaakType() {
			this.loading = true

			zaakTypeStore.deleteZaakType({
				...zaakTypeStore.zaakTypeItem,
			}).then(({ response }) => {
				this.success = response.ok
				response.ok && (this.closeModalTimeout = setTimeout(this.closeDialog, 2000))
			}).catch((error) => {
				this.success = false
				this.error = error.message || 'Er is een fout opgetreden bij het verwijderen van het zaaktype'
			}).finally(() => {
				this.loading = false
			})
		},
	},
}
</script>
