<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, klantStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					v-model="search"
					:show-trailing-button="search !== ''"
					:label="t('zaakafhandelapp', 'Search')"
					class="searchField"
					trailing-button-icon="close"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="klantStore.refreshKlantenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="klantStore.setKlantItem(null); navigationStore.setModal('editKlant')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Klant toevoegen
					</NcActionButton>
					<NcActionButton v-if="contactsAvailable" @click="navigationStore.setModal('importContact')">
						<template #icon>
							<ImportIcon :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Import from contacts') }}
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="klantStore.klantenList?.length">
				<NcListItem v-for="(klant, i) in klantStore.klantenList"
					:key="`${klant}${i}`"
					:name="getName(klant)"
					:active="$route.params?.id === klant?.id"
					:force-display-actions="true"
					:details="_.upperFirst(klant.type)"
					@click="openKlant(klant)">
					<template #icon>
						<AccountOutline disable-menu :size="44" />
					</template>
					<template #subname>
						{{ getSubname(klant) }}
					</template>
					<template #actions>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('editKlant')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!klantStore.klantenList?.length && !loading">
			Geen klanten gedefinieerd.
		</div>

		<NcLoadingIcon v-if="!klantStore.klantenList?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			:name="t('zaakafhandelapp', 'Loading customers')" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon, NcActions } from '@nextcloud/vue'
import _ from 'lodash'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import ImportIcon from 'vue-material-design-icons/Import.vue'

export default {
	name: 'KlantenList',
	components: {
		// Components
		NcListItem,
		NcActionButton,
		NcActions,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		AccountOutline,
		Magnify,
		Pencil,
		TrashCanOutline,
		Plus,
		Refresh,
		ImportIcon,
	},
	data() {
		return {
			search: '',
			loading: true,
			klantenList: [],
			contactsAvailable: false,
		}
	},
	/**
	 * @spec openspec/specs/ui-client-views/spec.md#REQ-005
	 */
	mounted() {
		klantStore.refreshKlantenList().then(() => {
			this.loading = false
		})
		this.checkContactsAvailability()
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-006
		 */
		checkContactsAvailability() {
			fetch('/index.php/apps/zaakafhandelapp/api/klanten/contacts/status', { method: 'GET' })
				.then(response => response.json())
				.then((data) => {
					this.contactsAvailable = data?.available === true
				})
				.catch(() => {
					this.contactsAvailable = false
				})
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-004
		 */
		openKlant(klant) {
			klantStore.setKlantItem(klant)
			this.$router.push({ params: { id: klant.id } })
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-005
		 */
		fullName(klant) {
			let name = klant.achternaam
			if (klant.tussenvoegsel) {
				name = `${klant.tussenvoegsel} ${name}`
			}
			if (klant.voornaam) {
				name = `${name}, ${klant.voornaam}`
			}
			return name
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-005
		 */
		getName(klant) {
			if (klant.type === 'persoon') {
				return klant?.voornaam ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-005
		 */
		getSubname(klant) {
			if (klant.type === 'persoon') {
				return klant?.tussenvoegsel ? `${klant.tussenvoegsel} ${klant.achternaam}` : klant?.achternaam ? `${klant.achternaam}` : 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.websiteUrl ?? 'onbekend'
			}
			return 'onbekend'
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-004
		 */
		deleteKlant() {
			fetch(
				`/index.php/apps/zaakafhandelapp/api/klanten/${klantStore.klantItem.id}`,
				{
					method: 'DELETE',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klantenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-001
		 */
		fetchData(newPage) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/klanten',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.klantenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-client-views/spec.md#REQ-003
		 */
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
