<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcDialog :name="t('zaakafhandelapp', 'Import from contacts')"
		size="normal"
		label-id="importContactModal"
		dialog-classes="importContactModal"
		:close-on-click-outside="false"
		@closing="closeModal">
		<div class="searchContainer">
			<NcTextField :value.sync="query"
				:disabled="loading"
				:label="t('zaakafhandelapp', 'Search contacts')"
				maxlength="255"
				class="searchField"
				@keydown.enter="search">
				<Magnify :size="20" />
			</NcTextField>
			<NcButton type="primary"
				:disabled="loading || !query"
				class="searchButton"
				@click="search">
				<template #icon>
					<Magnify :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Search') }}
			</NcButton>
		</div>

		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<NcNoteCard v-if="success" type="success">
			{{ success }}
		</NcNoteCard>

		<div class="resultsContainer">
			<div v-if="results.length && !loading">
				<NcListItem v-for="(contact, i) in results"
					:key="`${contact.uid}${i}`"
					:name="contact.name || contact.org || contact.email || contact.uid"
					:force-display-actions="true"
					:details="contact.alreadyLinked ? t('zaakafhandelapp', 'Linked') : ''">
					<template #icon>
						<OfficeBuildingOutline v-if="contact.org" disable-menu :size="44" />
						<AccountOutline v-else disable-menu :size="44" />
					</template>
					<template #subname>
						{{ [contact.email, contact.phone].filter(Boolean).join(' · ') }}
					</template>
					<template #actions>
						<NcActionButton :disabled="contact.alreadyLinked || importing"
							@click="importContact(contact)">
							<template #icon>
								<Import :size="20" />
							</template>
							{{ contact.alreadyLinked ? t('zaakafhandelapp', 'Already linked') : t('zaakafhandelapp', 'Import as customer') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>

			<div v-if="!results.length && searched && !loading" class="emptyResults">
				{{ t('zaakafhandelapp', 'No contacts found.') }}
			</div>

			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Searching')" />
		</div>

		<template #actions>
			<NcButton type="secondary" @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Close') }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcTextField, NcDialog, NcListItem, NcActionButton, NcLoadingIcon, NcNoteCard } from '@nextcloud/vue'

import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import Import from 'vue-material-design-icons/Import.vue'
import Cancel from 'vue-material-design-icons/Cancel.vue'

export default {
	name: 'ImportContact',
	components: {
		NcDialog,
		NcButton,
		NcTextField,
		NcListItem,
		NcActionButton,
		NcLoadingIcon,
		NcNoteCard,
		OfficeBuildingOutline,
		AccountOutline,
		Magnify,
		Import,
		Cancel,
	},
	data() {
		return {
			query: '',
			results: [],
			loading: false,
			importing: false,
			searched: false,
			error: '',
			success: '',
		}
	},
	methods: {
		/**
		 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-001
		 */
		search() {
			if (!this.query) {
				return
			}
			this.loading = true
			this.error = ''
			this.success = ''
			fetch(`/index.php/apps/zaakafhandelapp/api/klanten/contacts/search?query=${encodeURIComponent(this.query)}`, {
				method: 'GET',
			})
				.then(response => response.json())
				.then((data) => {
					this.results = Array.isArray(data) ? data : []
					this.searched = true
				})
				.catch((err) => {
					console.error(err)
					this.error = t('zaakafhandelapp', 'Could not search contacts.')
				})
				.finally(() => {
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/klanten-addressbook-sync/spec.md#REQ-002
		 */
		importContact(contact) {
			if (contact.alreadyLinked) {
				return
			}
			this.importing = true
			this.error = ''
			this.success = ''
			fetch('/index.php/apps/zaakafhandelapp/api/klanten/contacts/import', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ uid: contact.uid }),
			})
				.then(async (response) => {
					const data = await response.json()
					if (!response.ok) {
						throw new Error(data?.error || t('zaakafhandelapp', 'Import failed'))
					}
					return data
				})
				.then(() => {
					contact.alreadyLinked = true
					this.success = t('zaakafhandelapp', 'Contact imported as customer.')
					klantStore.refreshKlantenList()
				})
				.catch((err) => {
					console.error(err)
					this.error = err.message
				})
				.finally(() => {
					this.importing = false
				})
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModal() {
			navigationStore.setModal(null)
		},
	},
}
</script>

<style scoped>
.searchContainer {
	display: flex;
	align-items: end;
	gap: 10px;
	margin-block-end: 10px;
}

.searchField {
	flex: 1;
}

.searchButton {
	min-width: min-content !important;
}

.resultsContainer {
	border-top: 1px solid var(--color-border);
	padding-block: 20px;
	margin-block-start: 10px;
	min-height: 120px;
}

.emptyResults {
	color: var(--color-text-maxcontrast);
	text-align: center;
	padding-block: 20px;
}

.loadingIcon {
	margin-block-start: var(--zaa-margin-20, 20px);
}
</style>
