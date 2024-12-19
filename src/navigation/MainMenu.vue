<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<NcAppNavigation>
		<NcAppNavigationList>
			<NcAppNavigationNew
				text="Zaak Starten"
				@new-item="navigationStore.modal = 'zaakAdd'">
				<template #icon>
					<Plus :size="20" />
				</template>
			</NcAppNavigationNew>
			<router-link to="/">
				<NcAppNavigationItem
					:active="navigationStore.selected === 'dashboard'"
					name="Dashboard"
					@click="navigationStore.setSelected('dashboard')">
					<template #icon>
						<Finance :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/zaken">
				<NcAppNavigationItem
					:active="navigationStore.selected === 'zaken'"
					name="Zaken"
					@click="() => {}">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'taken'"
				name="Taken"
				@click="navigationStore.setSelected('taken')">
				<template #icon>
					<CalendarMonthOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'klanten'"
				name="Klanten"
				@click="navigationStore.setSelected('klanten')">
				<template #icon>
					<AccountGroupOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'medewerkers'"
				name="Medewerkers"
				@click="navigationStore.setSelected('medewerkers')">
				<template #icon>
					<AccountGroupOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'contactMomenten'"
				name="Contact momenten"
				@click="navigationStore.setSelected('contactMomenten')">
				<template #icon>
					<CardAccountPhoneOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'berichten'"
				name="Berichten"
				@click="navigationStore.setSelected('berichten')">
				<template #icon>
					<ChatOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'search'"
				name="Zoeken"
				@click="navigationStore.setSelected('search')">
				<template #icon>
					<Magnify :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationList>

		<NcAppNavigationSettings>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'zaakTypen'"
				name="Zaak Typen"
				@click="navigationStore.setSelected('zaakTypen')">
				<template #icon>
					<AlphaTBoxOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				:active="navigationStore.selected === 'auditTrail'"
				name="Audit trail"
				@click="navigationStore.setSelected('auditTrail')">
				<template #icon>
					<SortVariantLock :size="20" />
				</template>
			</NcAppNavigationItem>
			<Configuration />
		</NcAppNavigationSettings>
	</NcAppNavigation>
</template>
<script>

import {
	NcAppNavigation,
	NcAppNavigationList,
	NcAppNavigationItem,
	NcAppNavigationNew,
	NcAppNavigationSettings,
} from '@nextcloud/vue'

// Configuration
import Configuration from './Configuration.vue'

// Icons
import Finance from 'vue-material-design-icons/Finance.vue'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline.vue'
import ChatOutline from 'vue-material-design-icons/ChatOutline.vue'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline.vue'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import SortVariantLock from 'vue-material-design-icons/SortVariantLock.vue'
import Magnify from 'vue-material-design-icons/Magnify.vue'
import CardAccountPhoneOutline from 'vue-material-design-icons/CardAccountPhoneOutline.vue'

export default {
	name: 'MainMenu',
	components: {
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNew,
		NcAppNavigationSettings,
		// Icons
		Magnify,
		Finance,
		AlphaTBoxOutline,
		ChatOutline,
		AccountGroupOutline,
		CalendarMonthOutline,
		BriefcaseAccountOutline,
		Plus,
		SortVariantLock,
		Configuration,
		CardAccountPhoneOutline,
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
