<script setup>
import { navigationStore, klantStore } from '../../store/store.js'
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
							Bewerken
						</NcActionButton>
						<NcActionButton @click="klantStore.setKlantItem(klant); navigationStore.setModal('deleteKlant')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
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
			name="Klanten aan het laden" />
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

export default {
	name: 'KlantenList',
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
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			klantenList: [],
		}
	},
	mounted() {
		klantStore.refreshKlantenList().then(() => {
			this.loading = false
		})
	},
	methods: {
		openKlant(klant) {
			klantStore.setKlantItem(klant)
			this.$router.push({ params: { id: klant.id } })
		},
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
		getName(klant) {
			if (klant.type === 'persoon') {
				return klant?.voornaam ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},
		getSubname(klant) {
			if (klant.type === 'persoon') {
				return klant?.tussenvoegsel ? `${klant.tussenvoegsel} ${klant.achternaam}` : klant?.achternaam ? `${klant.achternaam}` : 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.websiteUrl ?? 'onbekend'
			}
			return 'onbekend'
		},
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
