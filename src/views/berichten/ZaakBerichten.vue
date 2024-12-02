<script setup>
import { navigationStore, berichtStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="!loading">
			<NcListItem v-for="(bericht, i) in berichtenList"
				:key="`${bericht}${i}`"
				:name="bericht?.onderwerp"
				:active="berichtStore.berichtItem?.id === bericht.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="toggleBericht(bericht)">
				<template #icon>
					<ChatOutline :class="berichtStore.berichtItem?.id === bericht.id && 'selectedZaakIcon'"
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
		</div>

		<div v-if="!berichtenList?.length && !loading">
			Geen berichten gevonden.
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Berichten aan het laden" />
	</div>
</template>
<script>
// Components
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'

export default {
	name: 'ZaakBerichten',
	components: {
		NcListItem,
		NcActionButton,
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
		zaakId(newVal) {
			this.fetchData(newVal)
		},
	},
	mounted() {
		this.fetchData(this.zaakId)
	},
	methods: {
		editBericht(bericht) {
			berichtStore.setBerichtItem(bericht)
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
						this.berichtenList = data.results || []
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		toggleBericht(bericht) {
			if (berichtStore.berichtItem?.id === bericht.id) {
				berichtStore.setBerichtItem(null)
			} else {
				berichtStore.setBerichtItem(bericht)
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
