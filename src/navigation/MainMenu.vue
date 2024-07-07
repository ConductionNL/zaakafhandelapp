<script setup>
import { store } from '../store.js'
</script>

<template>
	<NcAppNavigation>
		<NcAppNavigationList>
			<NcAppNavigationNewItem name="Zaak Aanmaken" @new-item="store.modal = 'zaakAdd'">
				<template #icon>
					<Plus :size="20" />
				</template>
			</NcAppNavigationNewItem>
			<NcAppNavigationItem 
				:active="store.selected === 'dashboard'" 
				icon="Finance" name="Dashboard"
				@click="store.setSelected('dashboard')">
			</NcAppNavigationItem>
			<NcAppNavigationItem 
				:active="store.selected.selected === 'zaken'" 
				icon="BriefcaseAccountOutline" 
				name="Zaken"
				@click="store.setSelected('zaken')">
			</NcAppNavigationItem>
			<NcAppNavigationItem 
				:active="store.selected === 'taken'" 
				icon="CalendarMonthOutline" 
				name="Taken"
				@click="store.setSelected('taken')">
			</NcAppNavigationItem>
			<NcAppNavigationItem 
				:active="store.selected === 'klanten'" 
				icon="AccountGroupOutline" 
				name="Klanten"
				@click="store.setSelected('klanten')">				
			</NcAppNavigationItem>
			<NcAppNavigationItem 
				:active="store.selected === 'berichten'" 
				icon="ChatOutline" 
				name="Berichten"
				@click="store.setSelected('berichten')">
			</NcAppNavigationItem>
		</NcAppNavigationList>

		<NcAppNavigationSettings>
			<NcAppNavigationItem 
				:active="store.selected === 'zaakTypen'" 
				name="zaakTypen" 
				icon="AlphaTBoxOutline"
				@click="store.setSelected('zaakTypen')">
			</NcAppNavigationItem>

			<NcAppNavigationItem name="Configuration" @click="settingsOpen = true">
				<template #icon>
					<CogOutline :size="20" />
				</template>
			</NcAppNavigationItem>

			<Configuration />
			
		</NcAppNavigationSettings>
	</NcAppNavigation>
</template>
<script>

import {
	NcActions,
	NcActionButton,
	NcAppNavigation,
	NcAppNavigationList,
	NcAppNavigationItem,
	NcAppNavigationNewItem
} from '@nextcloud/vue'

import Configuration from './Configuration.vue'

// Icons
import Plus from 'vue-material-design-icons/Plus'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'
import ChatOutline from 'vue-material-design-icons/ChatOutline'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline'
import CogOutline from 'vue-material-design-icons/CogOutline.vue'
import ContentSave from 'vue-material-design-icons/ContentSave.vue'


export default {
	name: 'MainMenu',
	components: {
		NcActions,
		NcActionButton,
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		Configuration,
		Plus,
		AccountGroupOutline,
		CalendarMonthOutline,
		BriefcaseAccountOutline,
		BriefcaseOutline,
		CogOutline,
		ContentSave
	},
}
</script>
<style>
table {
	table-layout: fixed;
}

td.row-name {
	padding-inline-start: 16px;
}

td.row-size {
	text-align: right;
	padding-inline-end: 16px;
}

.table-header {
	font-weight: normal;
	color: var(--color-text-maxcontrast);
}

.sort-icon {
	color: var(--color-text-maxcontrast);
	position: relative;
	inset-inline: -10px;
}

.row-size .sort-icon {
	inset-inline: 10px;
}
</style>
