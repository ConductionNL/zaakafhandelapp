<script setup>
import { translate as t } from '@nextcloud/l10n'
</script>

<template>
	<NcAppSidebar
		:name="t('zaakafhandelapp', 'Quick start')"
		:subname="t('zaakafhandelapp', 'Quickly navigate to where you need to be')">
		<NcAppSidebarTab id="search-tab" :name="t('zaakafhandelapp', 'Work instructions')" :order="1">
			<template #icon>
				<Magnify :size="20" />
			</template>
		</NcAppSidebarTab>
		<NcAppSidebarTab id="settings-tab" :name="t('zaakafhandelapp', 'Your cases')" :order="2">
			<template #icon>
				<BriefcaseAccountOutline :size="20" />
			</template>
			<NcNoteCard type="success">
				<p>{{ t('zaakafhandelapp', 'You currently have no open cases') }}</p>
			</NcNoteCard>
		</NcAppSidebarTab>
		<NcAppSidebarTab id="share-tab" :name="t('zaakafhandelapp', 'Your tasks')" :order="3">
			<template #icon>
				<CalendarMonthOutline :size="20" />
			</template>
			<NcNoteCard type="success">
				<p>{{ t('zaakafhandelapp', 'You currently have no open tasks') }}</p>
			</NcNoteCard>
		</NcAppSidebarTab>
	</NcAppSidebar>
</template>
<script>

import { NcAppSidebar, NcAppSidebarTab, NcNoteCard } from '@nextcloud/vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'

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
