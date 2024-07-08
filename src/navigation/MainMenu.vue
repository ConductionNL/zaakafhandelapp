<script setup>
import { store } from '../store.js'
</script>

<template>
	<NcAppNavigation>
		<NcAppNavigationList>
			<NcAppNavigationNewItem
				name="Zaak Starten"
				@new-item="store.modal = 'zaakAdd'">
				<template #icon>
					<Plus :size="20" />
				</template>
			</NcAppNavigationNewItem>
			<NcAppNavigationItem
				:active="store.selected === 'dashboard'"
				name="Dashboard"
				@click="store.setSelected('dashboard')">
				<template #icon>
					<Finance :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="store.selected === 'zaken'"
				name="Zaken"
				@click="store.setSelected('zaken')">
				<template #icon>
					<BriefcaseAccountOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="store.selected === 'taken'"
				name="Taken"
				@click="store.setSelected('taken')">
				<template #icon>
					<CalendarMonthOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="store.selected === 'klanten'"
				name="Klanten"
				@click="store.setSelected('klanten')">
				<template #icon>
					<AccountGroupOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="store.selected === 'berichten'"
				name="Berichten"
				@click="store.setSelected('berichten')">
				<template #icon>
					<ChatOutline :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationList>

		<NcAppNavigationSettings>
			<NcAppNavigationItem
				:active="store.selected === 'zaakTypen'"
				name="zaak Typen"
				@click="store.setSelected('zaakTypen')">
				<template #icon>
					<AlphaTBoxOutline :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationSettings>
	</NcAppNavigation>
</template>
<script>

import {
	NcAppNavigation,
	NcAppNavigationList,
	NcAppNavigationItem,
	NcAppNavigationNewItem,
	NcAppNavigationSettings
} from '@nextcloud/vue'

// Icons
import Finance from 'vue-material-design-icons/Finance.vue'

export default {
	name: 'MainMenu',
	components: {
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcAppNavigationSettings,
		Finance
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
