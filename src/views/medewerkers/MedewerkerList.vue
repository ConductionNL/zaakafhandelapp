<script setup>
import { navigationStore, medewerkerStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value.sync="search"
					:show-trailing-button="search !== ''"
					label="Search"
					class="searchField"
					trailing-button-icon="close"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="medewerkerStore.refreshMedewerkersList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="medewerkerStore.setMedewerkerItem(null); navigationStore.setModal('editMedewerker')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Medewerker toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="medewerkerStore.medewerkersList?.length && !loading">
				<NcListItem v-for="(medewerker, i) in medewerkerStore.medewerkersList"
					:key="`${medewerker}${i}`"
					:name="getName(medewerker)"
					:active="medewerkerStore.medewerkerItem?.id === medewerker?.id"
					:force-display-actions="true"
					:details="_.upperFirst(medewerker.type)"
					@click="medewerkerStore.setMedewerkerItem(medewerker)">
					<template #icon>
						<AccountOutline :class="medewerkerStore.medewerkerItem === medewerker.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ medewerker.email }}
					</template>
					<template #actions>
						<NcActionButton @click="medewerkerStore.setMedewerkerItem(medewerker); navigationStore.setModal('editMedewerker')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!medewerkerStore.medewerkersList?.length && !loading">
			Geen medewerkers gedefinieerd.
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Medewerkers aan het laden" />
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

export default {
	name: 'MedewerkerList',
	components: {
		// Components
		NcListItem,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		AccountOutline,
		Magnify,
		Pencil,
	},
	data() {
		return {
			search: '',
			loading: true,
			medewerkersList: [],
		}
	},
	mounted() {
		medewerkerStore.refreshMedewerkersList().then(() => {
			this.loading = false
		})
	},
	methods: {
		fullName(medewerker) {
			let name = medewerker.achternaam
			if (medewerker.tussenvoegsel) {
				name = `${medewerker.tussenvoegsel} ${name}`
			}
			if (medewerker.voornaam) {
				name = `${name}, ${medewerker.voornaam}`
			}
			return name
		},
		getName(medewerker) {
			return `${medewerker?.voornaam} ${medewerker?.tussenvoegsel} ${medewerker?.achternaam}`
		},
		deleteKlant() {
			fetch(
				`/index.php/apps/zaakafhandelapp/api/medewerkers/${medewerkerStore.medewerkerItem.id}`,
				{
					method: 'DELETE',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.medewerkersList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		fetchData(newPage) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/medewerkers',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.medewerkersList = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
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
