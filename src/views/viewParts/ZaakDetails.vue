<template>
	<NcAppSidebar
		:starred="starred"
		name="cat-picture.jpg"
		subname="last edited 3 weeks ago">
		<NcAppSidebarTab name="Search" id="search-tab">
			<template #icon>
				<Magnify :size="20" />
			</template>
			Search tab content
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Settings" id="settings-tab">
			<template #icon>
				<Cog :size="20" />
			</template>
			Settings tab content
		</NcAppSidebarTab>
		<NcAppSidebarTab name="Sharing" id="share-tab">
			<template #icon>
				<ShareVariant :size="20" />
			</template>
			Sharing tab content
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import Magnify from 'vue-material-design-icons/Magnify.vue'
import Cog from 'vue-material-design-icons/Cog.vue'
import ShareVariant from 'vue-material-design-icons/ShareVariant.vue'
import { NcAppSidebar,NcAppSidebarTab } from '@nextcloud/vue';

export default {
	name: "ZaakDetails",
	components: {
		Magnify,
		Cog,
		ShareVariant,
		NcAppSidebar,
		NcAppSidebarTab
	},
	data() {
		return {
			starred: false,
		}
	},
}
</script>
