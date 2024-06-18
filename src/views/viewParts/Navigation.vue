<template>
	<NcAppNavigation>
		<NcAppNavigationList>
			<NcAppNavigationNewItem name="Zaak Aanmaken" icon="" @new-item="function (value) { alert(value) }">
				<template #icon>
					<Plus :size="20" />
				</template>
			</NcAppNavigationNewItem>
			<NcAppNavigationItem :active="selected === 'zaken'" icon="" name="Zaken"
				href="/index.php/apps/dsonextcloud/zaken">
				<template #icon>
					<FolderOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="selected === 'taken'" icon="" name="Taken"
				href=" /index.php/apps/dsonextcloud/taken">
				<template #icon>
					<CalendarMonthOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="selected === 'klanten'" icon="" name="Klanten"
				href=" /index.php/apps/dsonextcloud/klanten">
				<template #icon>
					<BriefcaseAccountOutline :size="20" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem :active="selected === 'contact_momenten'" icon="" name="Contact Momenten"
				href=" /index.php/apps/dsonextcloud/contact_momenten">
				<template #icon>
					<ChatOutline :size="20" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigationList>



		<NcAppNavigationSettings>
			<NcAppNavigationItem :active="selected === 'zaak_typen'" icon="" name="Zaak Typen"
				href=" /index.php/apps/dsonextcloud/zaak_typen">
				<template #icon>
					<AlphaTBoxOutline :size="20" />
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem :active="selected === 'configuration'" icon="" name="Configuration"
				href=" /index.php/apps/dsonextcloud/configuration">
				<template #icon>
					<DatabaseCogOutline :size="20" />
				</template>
			</NcAppNavigationItem>


		</NcAppNavigationSettings>
	</NcAppNavigation>
</template>
<script>
import { NcAppNavigation, NcAppNavigationList, NcAppNavigationItem, NcAppNavigationNewItem, NcAppNavigationSettings } from '@nextcloud/vue';
import Plus from 'vue-material-design-icons/Plus'
import FolderOutline from 'vue-material-design-icons/FolderOutline'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'
import ChatOutline from 'vue-material-design-icons/ChatOutline'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline'
import DatabaseCogOutline from 'vue-material-design-icons/DatabaseCogOutline'

export default {
	name: "Navigation",
	props: [
		'selected'
	],
	components: {
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcAppNavigationSettings,
		Plus,
		FolderOutline,
		CalendarMonthOutline,
		ChatOutline,
		BriefcaseAccountOutline,
		AlphaTBoxOutline,
		DatabaseCogOutline
	}
}
</script>
<style></style>