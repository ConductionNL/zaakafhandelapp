<script setup>
import { translate as t } from '@nextcloud/l10n'
import { resultaatStore, navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcDialog :name="t('zaakafhandelapp', 'Delete result')"
		size="normal"
		:can-close="false">
		<!-- if zaken list is not populated yet, show a quick loading icon (this should under normal conditions not happen) -->
		<div v-if="!zaakStore.zakenList?.length">
			<NcLoadingIcon :size="40" />
		</div>
		<div v-else>
			<p v-if="success === null">
				Weet u zeker dat u
				<b>{{ zaakStore.zakenList.find((zaak) => zaak.id === resultaatStore.resultaatItem?.zaak)?.identificatie }} > {{ resultaatStore.resultaatItem?.toelichting }}</b>
				permanent wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.
			</p>

			<div v-if="success !== null">
				<NcNoteCard v-if="success" type="success">
					<p>{{ t('zaakafhandelapp', 'Result successfully deleted') }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="!success && !error" type="error">
					<p>{{ t('zaakafhandelapp', 'An error occurred while deleting the result') }}</p>
				</NcNoteCard>
				<NcNoteCard v-if="error" type="error">
					<p>{{ error }}</p>
				</NcNoteCard>
			</div>
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
				@click="deleteResultaat()">
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
	name: 'DeleteResultaat',
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
	mounted() {
		zaakStore.refreshZakenList()
	},
	methods: {
		closeDialog() {
			navigationStore.setModal(null)
			clearTimeout(this.closeModalTimeout)
		},
		async deleteResultaat() {
			this.loading = true

			resultaatStore.deleteResultaat({
				...resultaatStore.resultaatItem,
			}).then(({ response }) => {
				this.success = response.ok
				response.ok && (this.closeModalTimeout = setTimeout(this.closeDialog, 2000))
			}).catch((error) => {
				this.success = false
				this.error = error.message || 'Er is een fout opgetreden bij het verwijderen van het resultaat'
			}).finally(() => {
				this.loading = false
			})
		},
	},
}
</script>
