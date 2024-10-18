<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField class="searchField"
					disabled
					:value.sync="search"
					label="Search"
					trailing-button-icon="close"
					:show-trailing-button="search !== ''"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="fetchData">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="navigationStore.setModal('addZaak')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Zaak toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="!loading">
				<NcListItem v-for="(zaak, i) in zakenList.results"
					:key="`${zaak}${i}`"
					:name="zaak?.identificatie"
					:force-display-actions="true"
					:active="store.zaakId === zaak?.uuid"
					:details="'1h'"
					:counter-number="44"
					@click="storeZaak(zaak)">
					<template #icon>
						<BriefcaseAccountOutline :class="store.zaakId === zaak.uuid && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ zaak?.zaaktype }}
					</template>
					<template #actions>
						<NcActionButton @click="editZaak(zaak)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton disabled>
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Zaken aan het laden" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
export default {
	name: 'ZakenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		BriefcaseAccountOutline,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			zakenList: [],
		}
	},
	watch: {
		search: {
			handler(search) {
				this.fetchDataWithSearch(search)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		fetchData() {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/zrc/zaken',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zakenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		fetchDataWithSearch(search) {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/zrc/zaken?${search}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zakenList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		editZaak(zaak) {
			store.setZaakItem(zaak)
			store.setZaakId(zaak.uuid)
			navigationStore.setModal('editZaak')
		},
		storeZaak(zaak) {
			store.setZaakItem(zaak)
			store.setZaakId(zaak.uuid)
		},
		clearText() {
			this.search = ''
		},
	},
}
</script>
<style>

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
