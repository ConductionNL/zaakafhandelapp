<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContentList>
		<ul v-if="!loading">
			<NcListItem v-for="(rollen, i) in rollenList.results"
				:key="`${rollen}${i}`"
				:name="rollen?.omschrijving"
				:active="store.rolId === rollen.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="setZaakRolItem(rollen.id)">
				<template #icon>
					<BriefcaseAccountOutline :class="store.rolId === rollen.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ rollen?.omschrijving }}
				</template>
				<template #actions>
					<NcActionButton @click="editRol(rollen)">
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
			name="Rollen aan het laden" />
	</NcAppContentList>
</template>
<script>
import { NcListItem, NcActionButton, NcAppContentList, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'ZaakRollen',
	components: {
		NcListItem,
		NcActionButton,
		NcAppContentList,
		BriefcaseAccountOutline,
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
			rollenList: [],
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
		editRol(rol) {
			store.setRolItem(rol)
			store.setRolId(rol.id)
			store.setModal('editRol')
		},
		fetchData(zaakId) {
			this.loading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/zrc/rollen?zaak.id=${zaakId}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.rollenList = data
					})
					this.loading = false
					console.log(this.loading)
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
