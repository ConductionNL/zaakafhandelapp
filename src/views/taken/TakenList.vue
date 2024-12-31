<script setup>
import { klantStore, navigationStore, taakStore } from '../../store/store.js'
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
					<NcActionButton @click="taakStore.refreshTakenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="taakStore.setTaakItem(null); navigationStore.setModal('editTaak')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Taak toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="taakStore.takenList?.length && users && !loading">
				<NcListItem v-for="(taak, i) in taakStore.takenList"
					:key="`${taak}${i}`"
					:name="taak?.title"
					:force-display-actions="true"
					:active="$route.params?.id === taak?.id"
					:details="taak.status"
					:counter-number="taak.deadline ? `${Math.ceil((new Date(taak.deadline) - new Date()) / (1000 * 60 * 60 * 24))} dagen` : 'no deadline'"
					@click="openTaak(taak)">
					<template #icon>
						<CalendarMonthOutline disable-menu :size="44" />
					</template>
					<template #subname>
						{{ getName(taak) }}
					</template>
					<template #actions>
						<NcActionButton @click="taakStore.setTaakItem(taak); navigationStore.setModal('editTaak')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="taakStore.setTaakItem(taak); navigationStore.setDialog('deleteTaak')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!taakStore.takenList?.length && !loading">
			Geen taken gedefinieerd.
		</div>

		<NcLoadingIcon v-if="!taakStore.takenList?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Taken aan het laden" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'TakenList',
	components: {
		// Components
		NcListItem,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		CalendarMonthOutline,
		Magnify,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			takenList: [],
			users: null,
		}
	},
	mounted() {
		Promise.all([
			this.getUsers(),
			taakStore.refreshTakenList(),
			klantStore.refreshKlantenList(),
		]).then(() => {
			this.loading = false
		})
	},
	methods: {
		clearText() {
			this.search = ''
		},
		getUsers() {
			fetch('/ocs/v1.php/cloud/users/details', {
				method: 'GET',
				headers: {
					Accept: 'application/json',
					'OCS-APIRequest': 'true',
				},
			}).then(response => response.json()).then(data => {

				this.users = Object.values(data.ocs.data.users)
			})
		},
		openTaak(taak) {
			taakStore.setTaakItem(taak)
			this.$router.push({ params: { id: taak.id } })
		},
		getName(taak) {
			const medewerker = this.users.find(user => user.email === taak.medewerker)
			const klant = klantStore.klantenList.find(klant => klant.id === taak.klant)

			if (medewerker) {
				return medewerker.displayname ?? 'onbekend'
			}
			if (klant) {
				if (klant.type === 'persoon') {
					return `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}` ?? 'onbekend'
				}
				if (klant.type === 'organisatie') {
					return klant?.bedrijfsnaam ?? 'onbekend'
				}
			}
			return 'onbekend'
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
