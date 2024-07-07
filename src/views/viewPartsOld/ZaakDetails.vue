<template>
	<NcEmptyContent name="Geen zaak" description="Nog geen zaak geselecteerd" class="zaakDetailsContainer">
		<template #icon>
			<BriefcaseAccountOutline />
		</template>
		<template #action>
			<NcButton type="primary" @click="showModal">
				Zaak aanmaken
			</NcButton>

			<ZaakDetailsModal />

		</template>
	</NcEmptyContent>
</template>

<script>
import ZaakDetailsModal from '../../modals/ZaakDetailsModal.vue';
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline';
import { isModalOpen } from '../../modals/modalContext';
import { NcEmptyContent, NcButton } from '@nextcloud/vue';

export default {
	name: "ZaakDetails",
	components: {
		NcEmptyContent,
		BriefcaseAccountOutline,
		NcButton,
		ZaakDetailsModal
	},
	methods: {
		showModal() {
			isModalOpen.zaakDetailsModal = true
		},
	}
}
</script>

<style>
.modal__content {
	margin: var(--zaa-margin-50);
	text-align: center;
}

.zaakDetailsContainer {
	margin-block-start: var(--zaa-margin-20);
	margin-inline-start: var(--zaa-margin-20);
	margin-inline-end: var(--zaa-margin-20);
}
</style>
