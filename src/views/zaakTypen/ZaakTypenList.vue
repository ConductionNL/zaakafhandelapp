<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcAppContentList>
		<ul>
			<div class="listHeader">
				<NcTextField
					:value.sync="search"
					:show-trailing-button="search !== ''"
					label="Search"
					trailing-button-icon="close"
					class="searchField"
					@trailing-button-click="clearText">
					<Magnify :size="20" />
				</NcTextField>
				<NcActions>
					<NcActionButton @click="zaakTypeStore.refreshZaakTypenList()">
						<template #icon>
							<Refresh :size="20" />
						</template>
						Ververs
					</NcActionButton>
					<NcActionButton @click="zaakTypeStore.setZaakTypeItem(null); navigationStore.setModal('zaaktypeForm')">
						<template #icon>
							<Plus :size="20" />
						</template>
						Zaaktype toevoegen
					</NcActionButton>
				</NcActions>
			</div>

			<div v-if="zaakTypeStore.zaakTypeList?.length && !loading">
				<NcListItem v-for="(zaaktype, i) in zaakTypeStore.zaakTypeList"
					:key="`${zaaktype}${i}`"
					:name="zaaktype?.identificatie"
					:force-display-actions="true"
					:active="zaakTypeStore.zaakTypeItem?.id === zaaktype?.id"
					:details="'1h'"
					:counter-number="44"
					@click="zaakTypeStore.setZaakTypeItem(zaaktype)">
					<template #icon>
						<AlphaTBoxOutline :class="zaakTypeStore.zaakTypeItem?.id === zaaktype?.id && 'selectedZaakIcon'"
							disable-menu
							:size="44" />
					</template>
					<template #subname>
						{{ zaaktype?.omschrijvingGeneriek }}
					</template>
					<template #actions>
						<NcActionButton @click="zaakTypeStore.setZaakTypeItem(zaaktype); navigationStore.setModal('zaaktypeForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</template>
				</NcListItem>
			</div>
		</ul>

		<div v-if="!zaakTypeStore.zaakTypeList?.length && !loading">
			Geen zaaktypen gedefinieerd.
		</div>

		<NcLoadingIcon v-if="loading"
			class="loadingIcon"
			:size="64"
			appearance="dark"
			name="Zaaktypen aan het laden" />
	</NcAppContentList>
</template>

<script>
// Components
import { NcListItem, NcActions, NcActionButton, NcAppContentList, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

// Icons
import Magnify from 'vue-material-design-icons/Magnify.vue'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'ZaakTypenList',
	components: {
		// Components
		NcListItem,
		NcActions,
		NcActionButton,
		NcAppContentList,
		NcTextField,
		NcLoadingIcon,
		// Icons
		AlphaTBoxOutline,
		Magnify,
		Refresh,
		Pencil,
	},
	data() {
		return {
			search: '',
			loading: false,
		}
	},
	mounted() {
		this.loading = true

		zaakTypeStore.refreshZaakTypenList()
			.then(() => {
				this.loading = false
			})
			.catch((err) => {
				console.error(err)
				this.loading = false
			})
	},
	methods: {
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
