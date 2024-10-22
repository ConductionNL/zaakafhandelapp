<script setup>
import { searchStore } from '../../store/store.js'
</script>

<template>
	<NcAppSidebar
		name="Zoek opdracht"
		subname="Via deze pagina kunt u zoeken binnen uw gemeente">
		<NcAppSidebarTab id="search-tab" name="Zoeken" :order="1">
			<template #icon>
				<Magnify :size="20" />
			</template>
			<NcTextField class="searchField"
				:value.sync="searchStore.search"
				label="Search" />
		</NcAppSidebarTab>
		<NcAppSidebarTab id="share-tab" name="Personen" :order="2">
			<template #icon>
				<AccountGroupOutline :size="20" />
			</template>
			<NcTextField class="searchField"
				:value.sync="searchStore.search"
				label="Naam" />
		</NcAppSidebarTab>
		<NcAppSidebarTab id="settings-tab" name="Organisaties" :order="3">
			<template #icon>
				<OfficeBuildingOutline :size="20" />
			</template>
			<NcTextField class="searchField"
				:value.sync="searchStore.search"
				label="Naam" />
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import { NcAppSidebar, NcAppSidebarTab, NcTextField } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'

export default {
	name: 'SearchSideBar',
	components: {
		NcAppSidebar,
		NcAppSidebarTab,
		NcTextField,
		// Icons
		Magnify,
		AccountGroupOutline,
		OfficeBuildingOutline,
	},
	data() {
		return {

			starred: false,
		}
	},
}
</script>
