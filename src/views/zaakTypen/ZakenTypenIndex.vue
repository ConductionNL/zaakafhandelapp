<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ZaakTypeList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!store.zaakTypeItem || !store.zaakTypeItem.id || store.selected != 'zaakTypen' "
				class="detailContainer"
				name="Geen Zaaktype"
				description="Nog geen zaaktype geselecteerd">
				<template #icon>
					<AlphaTBoxOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="store.setModal('addZaakType')">
						Zaaktype toeveogen
					</NcButton>
				</template>
			</NcEmptyContent>
			<ZaakTypeDetails v-if="store.zaakTypeItem && store.zaakTypeItem.id && store.selected === 'zaakTypen'" :zaaktype-id="store.zaakTypeItem" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ZaakTypeList from './ZaakTypenList.vue'
import ZaakTypeDetails from './ZaakTypeDetails.vue'
// eslint-disable-next-line n/no-missing-import
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline'

export default {
	name: 'ZakenTypenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ZaakTypeList,
		ZaakTypeDetails,
		AlphaTBoxOutline,
	},
}
</script>
