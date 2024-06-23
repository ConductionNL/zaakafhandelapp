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
					<BriefcaseAccountOutline :size="20" />
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
					<AccountGroupOutline :size="20" />
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

			<NcAppNavigationItem @click="settingsOpen = true" name="Configuration">
				<template #icon>
					<BriefcaseOutline :size="20" />
				</template>
			</NcAppNavigationItem>

			<NcAppSettingsDialog :open.sync="settingsOpen" :show-navigation="true" name="Application settings">
				<NcAppSettingsSection id="sharing" name="Connections" docUrl="zaakafhandel.app">
					<template #icon>
						<Connection :size="20" />
					</template>

					<p>
						Here you can set the details for varius connection
					</p>

					<p>
						<table>
							<tbody>
								<tr>
									<td class="row-name">ZRC</td>		
									<td>Location</td>
									<td><NcTextField :value.sync="zrc_location"
										id="zrc_location"
										:label-outside="true"
										placeholder="https://"/></td>
									<td>Key</td>
									<td><NcTextField :value.sync="zrc_key"
										id="zrc_key"
										:label-outside="true"
										placeholder="***"/></td>
								</tr>
							</tbody>
						</table>
					</p>
					<NcButton aria-label="Save" type="primary" wide>
						<template #icon>
							<ContentSave :size="20" />
						</template>
						Save
					</NcButton>

				</NcAppSettingsSection>

			</NcAppSettingsDialog>


		</NcAppNavigationSettings>
	</NcAppNavigation>
</template>
<script>
import { NcAppNavigation, NcAppNavigationList, NcAppNavigationItem, NcAppNavigationNewItem, NcAppNavigationSettings,NcAppSettingsDialog,NcAppSettingsSection,NcButton,NcTextField   } from '@nextcloud/vue';
import Plus from 'vue-material-design-icons/Plus'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'
import ChatOutline from 'vue-material-design-icons/ChatOutline'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline'  
import Connection from 'vue-material-design-icons/Connection'
import Check from 'vue-material-design-icons/Check' 
import ContentSave from 'vue-material-design-icons/ContentSave' 

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
		NcAppSettingsDialog,
		NcAppSettingsSection,
		NcTextField,
		NcButton,
		Connection,
		Check,
		Plus,
		BriefcaseOutline,
		CalendarMonthOutline,
		ChatOutline,
		BriefcaseAccountOutline,
		AlphaTBoxOutline,
		AccountGroupOutline,
		ContentSave
	},
	data() {
		return {
			settingsOpen: false,
			zrc_location: "",
			zrc_key: "",
			ztc_location: "",
			ztc_key: "",
			drc_location: "",
			drc_key: ""
		}
	},
	methods: {
		save() {
			this.zrc_location = ''
			this.zrc_key = ''
		}
	}
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
