<script setup>
import { navigationStore, rolStore } from '../../store/store.js'
</script>

<template>
	<div>
		<div v-if="filteredRollenList?.length">
			<NcListItem v-for="(rol, i) in filteredRollenList"
				:key="`${rol}${i}`"
				:name="rol?.url"
				:active="rolStore.rolItem?.id === rol.id"
				:details="rol?.betrokkeneType"
				:counter-number="rol?.omschrijvingGeneriek"
				:force-display-actions="true"
				@click="toggleRol(rol)">
				<template #icon>
					<BriefcaseAccountOutline :class="rolStore.rolItem?.id === rol.id && 'selectedZaakIcon'"
						disable-menu
						:size="44" />
				</template>
				<template #subname>
					{{ rol?.roltype }}
				</template>
				<template #actions>
					<NcActionButton @click="$router.push({ name: 'dynamic-view', params: { view: 'rollen', id: rol.id } })">
						<template #icon>
							<Eye :size="20" />
						</template>
						Bekijken
					</NcActionButton>
					<NcActionButton @click="rolStore.setRolItem(rol); rolStore.extraData.redirect = false; navigationStore.setModal('rolForm')">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Bewerken
					</NcActionButton>
					<NcActionButton @click="rolStore.setRolItem(rol); navigationStore.setModal('deleteRol')">
						<template #icon>
							<TrashCanOutline :size="20" />
						</template>
						Verwijderen
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

import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'ZaakRollen',
	components: {
		NcListItem,
		NcActionButton,
		BriefcaseAccountOutline,
		NcLoadingIcon,
	},
	props: {
		zaakUrl: {
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
			return rolStore.rollenList.filter((rol) => rol.zaak === this.zaakUrl)
		},
	},
	watch: {
		zaakUrl(newVal) {
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
