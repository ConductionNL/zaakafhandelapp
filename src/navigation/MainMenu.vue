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
					:active="$route.path === '/'"
					name="Dashboard">
					<template #icon>
						<Finance :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/zaken">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/zaken')"
					name="Zaken">
					<template #icon>
						<BriefcaseAccountOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/taken">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/taken')"
					name="Taken">
					<template #icon>
						<CalendarMonthOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/klanten">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/klanten')"
					name="Klanten">
					<template #icon>
						<AccountGroupOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/medewerkers">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/medewerkers')"
					name="Medewerkers">
					<template #icon>
						<AccountGroupOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/contactmomenten">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/contactmomenten')"
					name="Contact momenten">
					<template #icon>
						<CardAccountPhoneOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/berichten">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/berichten')"
					name="Berichten">
					<template #icon>
						<ChatOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/zoeken">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/zoeken')"
					name="Zoeken">
					<template #icon>
						<Magnify :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
		</NcAppNavigationList>

		<NcAppNavigationSettings>
			<router-link to="/zaaktypen">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/zaaktypen')"
					name="Zaak Typen">
					<template #icon>
						<AlphaTBoxOutline :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
			<router-link to="/auditTrail">
				<NcAppNavigationItem
					:active="$route.path.startsWith('/auditTrail')"
					name="Audit trail">
					<template #icon>
						<SortVariantLock :size="20" />
					</template>
				</NcAppNavigationItem>
			</router-link>
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
