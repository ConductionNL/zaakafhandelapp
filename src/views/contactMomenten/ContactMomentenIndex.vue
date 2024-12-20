<script setup>
import { navigationStore, contactMomentStore } from '../../store/store.js'
</script>

<template>
	<NcAppContent>
		<template #list>
			<ContactMomentenList />
		</template>
		<template #default>
			<NcEmptyContent v-if="!id"
				class="detailContainer"
				name="Geen contact moment"
				description="Nog geen contact moment geselecteerd">
				<template #icon>
					<CardAccountPhoneOutline />
				</template>
				<template #action>
					<NcButton type="primary"
						@click="contactMomentStore.setContactMomentItem(null); navigationStore.setModal('contactMomentenForm')">
						Contact moment aanmaken
					</NcButton>
				</template>
			</NcEmptyContent>
			<ContactMomentDetails v-if="id" :id="id" />
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
	data() {
		return {
			id: this.$route.params.id,
		}
	},
}
</script>
