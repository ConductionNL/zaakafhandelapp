<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcAppSidebar
		name="Snelle start"
		subname="Schakel snel naar waar u nodig bent">
		<NcAppSidebarTab id="search-tab" name="Werkinstructies" :order="1">
			<template #icon>
				<Magnify :size="20" />
			</template>
		</NcAppSidebarTab>
		<NcAppSidebarTab id="settings-tab" name="Uw Zaken" :order="2">
			<template #icon>
				<BriefcaseAccountOutline :size="20" />
			</template>
			<NcNoteCard type="success">
				<p>U heeft op dit moment geen openstaande zaken</p>
			</NcNoteCard>
		</NcAppSidebarTab>
		<NcAppSidebarTab id="share-tab" name="Uw taken" :order="3">
			<template #icon>
				<CalendarMonthOutline :size="20" />
			</template>
			<NcNoteCard type="success">
				<p>U heeft op dit moment geen openstaande taken</p>
			</NcNoteCard>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import { NcAppSidebar, NcAppSidebarTab, NcTextField, NcNoteCard, NcListItem, NcActionButton } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Publish from 'vue-material-design-icons/Publish.vue'
import Delete from 'vue-material-design-icons/Delete.vue'

export default {
	name: 'DashboardSideBar',
	components: {
		NcAppSidebar,
		NcAppSidebarTab,
		NcNoteCard,
		// Icons
		Magnify,
		BriefcaseAccountOutline,
		CalendarMonthOutline,
	},
	data() {
		return {

			publications: false,
			attachments: false,
		}
	},
}
</script>
