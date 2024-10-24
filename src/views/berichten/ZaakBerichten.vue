<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul v-if="!loading">
			<NcListItem v-for="(bericht, i) in berichtenList.results"
				:key="`${bericht}${i}`"
				:name="bericht?.onderwerp"
				:active="store.berichtId === bericht.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="store.setBerichtItem(bericht)">
				<template #icon>
					<ChatOutline :class="store.berichtId === bericht.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ bericht?.berichttekst }}
				</template>
				<template #actions>
					<NcActionButton @click="editBericht(bericht)">
						Bewerken
					</NcActionButton>
					<NcActionButton>
						Verwijderen
					</NcActionButton>
				</template>
			</NcListItem>
		</ul>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Berichten aan het laden" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcAppContentList, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'

export default {
	name: 'ZaakBerichten',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		ChatOutline,
		NcLoadingIcon,
	},
	props: {
		zaakId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			search: '',
			loading: true,
			berichtenList: [],
		}
	},
	watch: {
		zaakId: {
			handler(zaakId) {
				this.fetchData(zaakId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.zaakItem)
	},
	methods: {
		editBericht(bericht) {
			store.setBerichtItem(bericht)
			store.setBerichtId(bericht.id)
			navigationStore.setModal('editBericht')
		},
		fetchData(zaakId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/berichten',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.berichtenList = data
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
