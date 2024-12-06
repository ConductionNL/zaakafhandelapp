<script setup>
import { navigationStore, rolStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="filteredRollenList?.length">
			<NcListItem v-for="(rollen, i) in filteredRollenList"
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
					<NcActionButton @click="rolStore.setRolItem(rollen); navigationStore.setSelected('rollen')">
						<template #icon>
							<Eye :size="20" />
						</template>
						Bekijken
					</NcActionButton>
					<!-- <NcActionButton @click="rolStore.setRolItem(rollen); navigationStore.setModal('rollen')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton> -->
					<NcActionButton>
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen van zaak
					</NcActionButton>
				</template>
			</NcListItem>
		</div>

		<div v-if="!filteredRollenList?.length && !loading">
			Geen rollen gevonden.
		</div>

		<NcLoadingIcon v-if="!filteredRollenList?.length && loading"
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
		}
	},
	computed: {
		filteredRollenList() {
			return rolStore.rollenList.filter((rol) => rol.zaak === this.zaakId)
		},
	},
	watch: {
		zaakId(newVal) {
			this.fetchData()
		},
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		editRol(rol) {
			rolStore.setRolItem(rol)
			navigationStore.setModal('editRol')
		},
		fetchData() {
			this.loading = true

			rolStore.refreshRollenList()
				.finally(() => {
					this.loading = false
				})
		},
		toggleRol(rol) {
			if (rolStore.rolItem?.id === rol.id) {
				rolStore.setRolItem(null)
			} else {
				rolStore.setRolItem(rol)
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
