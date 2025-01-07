<script setup>
import { navigationStore, documentStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="documenten[zaakId]?.documenten?.length">
			<NcListItem v-for="(document, i) in documenten[zaakId]?.documenten"
				:key="`${document}${i}`"
				:name="document?.titel"
				:active="documentStore.documentItem?.id === document?.id"
				:force-display-actions="true"
				@click="toggleDocument(document)">
				<template #icon>
					<FileDocumentOutline :class="documentStore.documentItem?.id === document.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ document?.beschrijving }}
				</template>
				<template #actions>
					<NcActionButton @click="(documentStore.zaakId = zaakId); documentStore.setDocumentItem(document); navigationStore.setModal('documentForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<NcActionButton @click="(documentStore.zaakId = zaakId); documentStore.setDocumentItem(document); navigationStore.setModal('deleteDocument')">
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen van zaak
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!documenten[zaakId]?.documenten?.length && !documenten[zaakId]?.loading">
			Geen documenten gevonden.
		</div>

		<NcLoadingIcon v-if="!documenten[zaakId]?.documenten?.length && documenten[zaakId]?.loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Documenten aan het laden" />
	</div>
</template>

<script>
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

import FileDocumentOutline from 'vue-material-design-icons/FileDocumentOutline.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'ZaakDocumenten',
	components: {
		NcListItem,
		NcActionButton,
		FileDocumentOutline,
		NcLoadingIcon,
		TrashCanOutline,
		Pencil,
	},
	props: {
		zaakId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			// this is saved in a cache like system for easier navigation between zaken and to avoid unnecessary wait time for the end user
			// eg. documenten[zaakId] = { documenten: [], loading: false }
			documenten: {},
			search: '',
		}
	},
	watch: {
		zaakId(newVal) {
			this.fetchData(newVal)
		},
	},
	mounted() {
		this.fetchData(this.zaakId)
	},
	methods: {
		fetchData() {
			this.documenten = {
				...this.documenten,
				[this.zaakId]: {
					documenten: [],
					loading: true,
				},
			}

			documentStore.getDocumenten(this.zaakId)
				.then(({ data }) => {
					this.documenten[this.zaakId].documenten = data
				})
				.finally(() => {
					this.documenten[this.zaakId].loading = false
				})
		},
		toggleDocument(document) {
			if (documentStore.documentItem?.id === document.id) {
				documentStore.setDocumentItem(null)
			} else {
				documentStore.setDocumentItem(document)
			}
		},
		clearText() {
			this.search = ''
		},
	},
}
</script>

<style>
.listHeader {
	position: sticky;
	top: 0;
	z-index: 1000;
	background-color: var(--color-main-background);
	border-bottom: 1px solid var(--color-border);
}

.searchField {
	padding-inline-start: 65px;
	padding-inline-end: 20px;
	margin-block-end: 6px;
}

.selectedZaakIcon>svg {
	fill: white;
}

.loadingIcon {
	margin-block-start: var(--zaa-margin-20);
}
</style>
