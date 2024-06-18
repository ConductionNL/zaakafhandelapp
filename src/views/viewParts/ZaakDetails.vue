<template>
	<NcAppSidebar
		:starred="starred"
		name="Samenwerkings verzoek"
		subname="last edited 3 weeks ago">
		<NcAppSidebarTab name="Eigenschappen" id="eigenschappen-tab">
			<template #icon>
				<Magnify :size="20" />
			</template>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Documenten" id="documenten-tab">
			<template #icon>
				<Cog :size="20" />
			</template>
			<NcButton type="primary">
				Document toevoegen
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Rollen" id="rollen-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Rol aanmaken
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Objecten" id="objecten-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Object aanmaken
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Besluiten" id="besluiten-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Besluit aanmaken
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Taken" id="taken-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Taak aanmaken
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Contact Momenten" id="contact-momenten-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Contact moment aanmaken
			</NcButton>
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Publicaties" id="publicaties-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			<NcButton type="primary">
				Publicatie aanmaken
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
