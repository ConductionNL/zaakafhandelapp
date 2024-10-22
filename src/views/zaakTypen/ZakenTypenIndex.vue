<script setup>
import { navigationStore, zaakTypeStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZaakTypeList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!zaakTypeStore.zaakTypeItem || navigationStore.selected != 'zaakTypen' "
				class="detailContainer"
				name="Geen Zaaktype"
				description="Nog geen zaaktype geselecteerd">
				<template #icon>
					<AlphaTBoxOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="zaakTypeStore.setZaakTypeItem(null); navigationStore.setModal('zaakTypeForm')">
						Zaaktype toevoegen
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakTypeDetails v-if="zaakTypeStore.zaakTypeItem && navigationStore.selected === 'zaakTypen'" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZaakTypeList from './ZaakTypenList.vue'
import ZaakTypeDetails from './ZaakTypeDetails.vue'
// eslint-disable-next-line n/no-missing-import
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline.vue'

export default {
	name: 'ZakenTypenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ZaakTypeList,
		ZaakTypeDetails,
	},
}
</script>
