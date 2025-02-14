<script setup>
import { navigationStore, contactMomentStore, klantStore } from '../../store/store.js'
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
					<NcActionButton @click="contactMomentStore.refreshContactMomentenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton
						@click="contactMomentStore.setContactMomentItem(null); navigationStore.setModal('contactMomentenForm')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Contact moment toevoegen
					</NcActionButton>
				</NcActions>
			</div>
			<div v-if="contactMomentStore.contactMomentenList?.length">
				<NcListItem v-for="(contactMoment, i) in contactMomentStore.contactMomentenList"
					:key="`${contactMoment}${i}`"
					:name="getKlantName(contactMoment.klant) || 'onbekend'"
					:active="$route.params?.id === contactMoment?.id"
					:force-display-actions="true"
					@click="openContactMoment(contactMoment)">
					<template #icon>
						<CardAccountPhoneOutline disable-menu :size="44" />
					</template>
					<template #subname>
						{{ new Date(contactMoment.startDate).toLocaleString() }}
					</template>
					<template #actions>
						<NcActionButton
							@click="contactMomentStore.setContactMomentItem(contactMoment); navigationStore.setModal('contactMomentenForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton
							@click="contactMomentStore.setContactMomentItem(contactMoment); navigationStore.setModal('deleteContactMoment')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!contactMomentStore.contactMomentenList?.length && !loading">
			Geen contact momenten gedefinieerd.
		</div>

		<NcLoadingIcon v-if="!contactMomentStore.contactMomentenList?.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Contact momenten aan het laden" />
	</NcAppContentList>
</template>
<script>
//  Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import CardAccountPhoneOutline from 'vue-material-design-icons/CardAccountPhoneOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'ContactMomentenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		Magnify,
		Refresh,
		Plus,
		CardAccountPhoneOutline,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: true,
			contactMomentenList: [],
		}
	},
	mounted() {
		Promise.all([
			contactMomentStore.refreshContactMomentenList(),
			klantStore.refreshKlantenList(),
		]).then(() => {
			this.loading = false
		})
	},
	methods: {
		openContactMoment(contactMoment) {
			contactMomentStore.setContactMomentItem(contactMoment)
			this.$router.push({ name: 'dynamic-view', params: { view: 'contactmomenten', id: contactMoment.id } })
		},
		editContactMoment(contactMoment) {
			contactMomentStore.setContactMomentItem(contactMoment)
			navigationStore.setModal('editContactMoment')
		},
		storeContactMoment(contactMoment) {
			contactMomentStore.setContactMomentItem(contactMoment)
		},
		getKlantName(klantId) {
			const klant = klantStore.klantenList.find(klant => klant.id === klantId)
			if (!klant) {
				return 'onbekend'
			}
			if (klant.type === 'persoon') {
				return `${klant.voornaam} ${klant.tussenvoegsel} ${klant.achternaam}` ?? 'onbekend'
			}
			if (klant.type === 'organisatie') {
				return klant?.bedrijfsnaam ?? 'onbekend'
			}
			return 'onbekend'
		},

		fetchData(newPage) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/contactMomenten',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.contactMomentenList = data
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
