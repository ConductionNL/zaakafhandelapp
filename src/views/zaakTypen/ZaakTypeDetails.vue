<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<NcLoadingIcon v-if="!zaakTypeStore.zaakTypeItem && loading" :size="64" />

			<div v-if="zaakTypeStore.zaakTypeItem">
				<div class="head">
					<h1 class="h1">
						{{ zaakTypeStore.zaakTypeItem?.identificatie }}
					</h1>

					<NcActions :primary="true" :menu-name="t('zaakafhandelapp', 'Actions')">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('zaaktypeForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Edit') }}
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('deleteZaaktype')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Delete') }}
						</NcActionButton>
					</NcActions>
				</div>

				<div class="detailGrid">
					<div>
						<h4>{{ t('zaakafhandelapp', 'Description:') }}</h4>
						<span>{{ zaakTypeStore.zaakTypeItem?.omschrijving || '-' }}</span>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Generic description:') }}</h4>
						<span>{{ zaakTypeStore.zaakTypeItem?.omschrijvingGeneriek || '-' }}</span>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Confidentiality designation:') }}</h4>
						<span>{{ zaakTypeStore.zaakTypeItem?.vertrouwelijkheidaanduiding || '-' }}</span>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Goal:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.doel || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Cause:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.aanleiding || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Explanation:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.toelichting || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Internal or external indication:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.indicatieInternOfExtern || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Action initiator:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.handelingInitiator || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Subject:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.onderwerp || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Action handler:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.handelingBehandelaar || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Lead time:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.doorlooptijd || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Service standard:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.servicenorm || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Suspension and stay possible:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.opschortingEnAanhoudingMogelijk || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Extension possible:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.verlengingMogelijk || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Extension term:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.verlengingstermijn || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Publication indication:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.publicatieIndicatie || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Publication text:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.publicatietekst || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Products or services:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.productenOfDiensten || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Selection list process type:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.selectielijstProcestype || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Reference process:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.referentieproces || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Catalogue:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.catalogus || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Start of validity:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.beginGeldigheid || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'End of validity:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.eindeGeldigheid || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Start of object:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.beginObject || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'End of object:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.eindeObject || '-' }}</p>
					</div>
					<div>
						<h4>{{ t('zaakafhandelapp', 'Version date:') }}</h4>
						<p>{{ zaakTypeStore.zaakTypeItem?.versiedatum || '-' }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'

export default {
	name: 'ZaakTypeDetails',
	components: {
		// Components
		NcLoadingIcon,
		NcActions,
		NcActionButton,
		// Icons
		DotsHorizontal,
		Pencil,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			currentId: null,
		}
	},
	watch: {
		id(newId) {
			this.fetchData(newId)
		},
	},
	mounted() {
		this.fetchData(this.id)
	},
	methods: {
		fetchData(id) {
			this.loading = true

			// get current zaaktype once
			zaakTypeStore.getZaakType(id, { setItem: true })
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>

<style scoped>
h4 {
	font-weight: bold;
}

.head {
	display: flex;
	justify-content: space-between;
}

.h1 {
	display: block !important;
	font-size: 2em !important;
	margin-block-start: 0.67em !important;
	margin-block-end: 0.67em !important;
	margin-inline-start: 0px !important;
	margin-inline-end: 0px !important;
	font-weight: bold !important;
	unicode-bidi: isolate !important;
}

.detailGrid {
	display: grid;
	grid-gap: 0.5rem;
	grid-template-columns: 1fr 1fr;
}
.detailGrid h4 {
    margin: 0 !important;
    font-size: initial !important;
}
</style>
