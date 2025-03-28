<script setup>
import { navigationStore, zaakStore, zaakTypeStore } from '../../store/store.js'
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
					<NcActionButton @click="zaakStore.setZaakItem(null); navigationStore.setModal('zaakForm')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Zaak starten
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="zaakStore.zakenList?.length">
				<NcListItem v-for="(zaak, i) in zaakStore.zakenList"
					:key="`${zaak}${i}`"
					:name="zaak?.identificatie"
					:force-display-actions="true"
					:active="$route.params?.id === zaak?.id"
					:details="'1h'"
					:counter-number="zaak.uiterlijkeEinddatumAfdoening ? `${Math.ceil((new Date(zaak.uiterlijkeEinddatumAfdoening) - new Date()) / (1000 * 60 * 60 * 24))} dagen` : 'no deadline'"
					@click="openZaak(zaak)">
					<template #icon>
						<BriefcaseAccountOutline :class="zaakStore.zaakItem?.id === zaak?.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ zaakTypeStore.zaakTypeList.find(zaakType => zaakType.id === zaak.zaaktype)?.identificatie ?? zaak.zaaktype }}
					</template>
					<template #actions>
						<NcActionButton @click="zaakStore.setZaakItem(zaak); navigationStore.setModal('zaakForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton disabled>
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!zaakStore.zakenList.length && !loading">
			Geen zaken gedefinieerd.
		</div>

		<NcLoadingIcon v-if="!zaakStore.zakenList.length && loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Zaken aan het laden" />
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
	name: 'ZakenList',
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
			zakenList: [],
		}
	},
	mounted() {
		this.loading = true

		Promise.all([
			zaakStore.refreshZakenList(),
			zaakTypeStore.refreshZaakTypenList(),
		]).then(() => {
			this.loading = false
		})
	},
	methods: {
		clearText() {
			this.search = ''
		},
		openZaak(zaak) {
			zaakStore.setZaakItem(zaak)
			this.$router.push({ params: { id: zaak.id } })
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

.selectedZaakIcon>svg {
    fill: white;
}

.loadingIcon {
    margin-block-start: var(--zaa-margin-20);
}
</style>
