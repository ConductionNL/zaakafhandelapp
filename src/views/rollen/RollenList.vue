<script setup>
import { navigationStore, rolStore } from '../../store/store.js'
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
					<NcActionButton @click="zaakStore.refreshZakenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="rolStore.setRolItem(null); navigationStore.setModal('rolForm')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Rol starten
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="rolStore.rollenList?.length">
				<NcListItem v-for="(rol, i) in rolStore.rollenList"
					:key="`${rol}${i}`"
					:name="rol?.roltype"
					:force-display-actions="true"
					:active="$route.params?.id === rol?.id"
					@click="openRol(rol)">
					<template #icon>
						<BriefcaseAccountOutline :class="rolStore.rolItem?.id === rol?.id && 'selectedRolIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ rol.roltoelichting }}
					</template>
					<template #actions>
						<NcActionButton @click="rolStore.setRolItem(rol); navigationStore.setModal('rolForm')">
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
		</ul>

		<div v-if="!rolStore.rollenList.length && !loading">
			Geen rollen gedefinieerd.
		</div>

		<NcLoadingIcon v-if="!rolStore.rollenList.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Rollen aan het laden" />
	</NcAppContentList>
</template>
<script>
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
export default {
	name: 'RollenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		BriefcaseAccountOutline,
		Magnify,
		Refresh,
		Plus,
		Pencil,
		TrashCanOutline,
	},
	data() {
		return {
			search: '',
			loading: false,
			rollenList: [],
		}
	},
	mounted() {
		this.loading = true

		Promise.all([
			rolStore.refreshRollenList(),
		]).then(() => {
			this.loading = false
		})
	},
	methods: {
		clearText() {
			this.search = ''
		},
		openRol(rol) {
			rolStore.setRolItem(rol)
			this.$router.push({ params: { id: rol.id } })
		},
	},
}
</script>

<style>
.searchField {
    padding-inline-start: 65px;
    padding-inline-end: 20px;
    margin-block-end: 6px;
}

.selectedRolIcon>svg {
    fill: white;
}

.loadingIcon {
    margin-block-start: var(--zaa-margin-20);
}
</style>
