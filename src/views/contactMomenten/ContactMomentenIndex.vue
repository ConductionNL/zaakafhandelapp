<script setup>
import { navigationStore, contactMomentStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ContactMomentenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!contactMomentStore.contactMomentItem?.id || navigationStore.selected !== 'contactMomenten'"
				class="detailContainer"
				name="Geen contact moment"
				description="Nog geen contact moment geselecteerd">
				<template #icon>
					<CardAccountPhoneOutline />
				</template>
				<template #action>
					<NcButton type="primary" @click="navigationStore.setModal('editContactMoment')">
						Contact moment aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<ContactMomentDetails v-if="contactMomentStore.contactMomentItem?.id && navigationStore.selected === 'contactMomenten'" :contact-moment-id="contactMomentStore.contactMomentItem?.id" />
		</template>
	</NcAppContent>
</template>

<script>
import { NcAppContent, NcEmptyContent, NcButton } from '@nextcloud/vue'
import ContactMomentenList from './ContactMomentenList.vue'
import ContactMomentDetails from './ContactMomentDetails.vue'
import CardAccountPhoneOutline from 'vue-material-design-icons/CardAccountPhoneOutline.vue'

export default {
	name: 'ContactMomentenIndex',
	components: {
		NcAppContent,
		NcEmptyContent,
		NcButton,
		ContactMomentenList,
		ContactMomentDetails,
		CardAccountPhoneOutline,
	},
}
</script>
