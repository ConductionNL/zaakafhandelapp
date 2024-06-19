<template>
	<NcAppSidebar
		:starred="starred"
		name="Verhandelingen"
		subname="last edited 3 weeks ago">
		<NcAppSidebarTab name="Taken" id="eigenschappen-tab">
			<template #icon>
				<Magnify :size="20" />
			</template>
			Taken List
			<NcButton type="primary">
				Taak toevoegen
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Contact" id="contact-tab">
			<template #icon>
				<Cog :size="20" />
			</template>
				Contact momenten list
			<NcButton type="primary">
				Contact moment toevoegen
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Publiceren" id="publiceren-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
				Publicatie list
			<NcButton type="primary">
				Publiceren
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Notities" id="notities-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			Notities
			<NcButton type="primary">
				notities
			</NcButton>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import Magnify from 'vue-material-design-icons/Magnify.vue'
import Cog from 'vue-material-design-icons/Cog.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import { NcAppSidebar,NcAppSidebarTab,NcButton } from '@nextcloud/vue';

export default {
	name: "ZaakDetails",
	components: {
		Magnify,
		Cog,
		ShareVariant,
		NcAppSidebar,
		NcAppSidebarTab,
		NcButton
	},
	data() {
		return {
			starred: false,
		}
	},
}
</script>
<style>
.app-sidebar {
	min-width: 800px;
}
</style>
