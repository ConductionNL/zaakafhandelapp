<script setup>
import { navigationStore, rolStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="!loading">
			<NcListItem v-for="(rollen, i) in rollenList"
				:key="`${rollen}${i}`"
				:name="rollen?.omschrijving"
				:active="rolStore.rolItem?.id === rollen.id"
				:details="'1h'"
				:counter-number="44"
				:force-display-actions="true"
				@click="toggleRol(rollen)">
				<template #icon>
					<BriefcaseAccountOutline :class="rolStore.rolItem?.id === rollen.id && 'selectedZaakIcon'"
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
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Rollen aan het laden" />
	</div>
</template>
<script>
import { NcListItem, NcActionButton, NcLoadingIcon } from '@nextcloud/vue'
// eslint-disable-next-line n/no-missing-import
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'

export default {
	name: 'ZaakRollen',
	components: {
		NcListItem,
		NcActionButton,
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
		zaakId(newVal) {
			this.fetchData(newVal)
		},
	},
	mounted() {
		this.fetchData(this.zaakId)
	},
	methods: {
		editRol(rol) {
			rolStore.setRolItem(rol)
			navigationStore.setModal('editRol')
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
						this.rollenList = data.results
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		toggleRol(rol) {
			// TODO: toggle rol
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
