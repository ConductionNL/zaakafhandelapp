<script setup>
import { store } from '../store.js'
</script>

<template>
	<NcAppSettingsDialog :open.sync="settingsOpen" :show-navigation="true" name="Application settings">
		<NcAppSettingsSection id="sharing" name="Storage" doc-url="zaakafhandel.app">
			<template #icon>
				<Connection :size="20" />
			</template>

			<p>
				The ZaakAfhandelApp allows three types of storage:
				
				<ul>
					<li>In the nexcloud database (default)</li>
					<li>In an seperate object store e.g. monogodb (recomended for small organisations)</li>
					<li>In seperate zgw registers e.g ZRR, ZDC (recomended of medium and up organisations)</li>
				</ul>

				The default storage option (in the nextcloud database) works fine for defelopment and demo experiences but should not be brought into production.
			</p>

		</NcAppSettingsSection>
		<NcAppSettingsSection id="sharing" name="Connections" doc-url="zaakafhandel.app">
			<template #icon>
				<Connection :size="20" />
			</template>

			<p>
				Here you can set the details for varius Connections
			</p>
			
		</NcAppSettingsSection>
		<NcAppSettingsSection id="organisation" name="Organisation" doc-url="zaakafhandel.app">
			<template #icon>
				<Connection :size="20" />
			</template>

			<p>
				Here you can set the details for your organisation
			</p>

			
		</NcAppSettingsSection>
	</NcAppSettingsDialog>
</template>
<script>

import {
	NcActions,
	NcActionButton,
	NcAppNavigation,
	NcAppNavigationList,
	NcAppNavigationItem,
	NcAppNavigationNewItem,
	NcAppNavigationSettings,
	NcAppSettingsDialog,
	NcAppSettingsSection,
	NcButton,
	NcTextField,
	NcTextArea,
} from '@nextcloud/vue'

import Connection from 'vue-material-design-icons/Connection.vue'


import Plus from 'vue-material-design-icons/Plus'
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline'
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline'
import ChatOutline from 'vue-material-design-icons/ChatOutline'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline'
import AlphaTBoxOutline from 'vue-material-design-icons/AlphaTBoxOutline'
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline'
import CogOutline from 'vue-material-design-icons/CogOutline.vue'
import ContentSave from 'vue-material-design-icons/ContentSave.vue'
import DatabaseEyeOutline from 'vue-material-design-icons/DatabaseEyeOutline.vue'
import DatabaseCogOutline from 'vue-material-design-icons/DatabaseCogOutline.vue'
import LayersSearchOutline from 'vue-material-design-icons/LayersSearchOutline.vue'  
import LayersOutline from 'vue-material-design-icons/LayersOutline.vue' 
import FileTreeOutline from 'vue-material-design-icons/FileTreeOutline.vue' 
import Finance from 'vue-material-design-icons/Finance.vue' 






export default {
	name: 'MainMenu',
	components: {
		NcActions,
		NcActionButton,
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcAppNavigationSettings,
		NcAppSettingsDialog,
		NcAppSettingsSection,
		NcTextField,
		NcTextArea,
		NcButton,
		Plus,
		Connection,
		DatabaseEyeOutline,
		DatabaseCogOutline,
		LayersSearchOutline,
		LayersOutline,
		FileTreeOutline,
		CogOutline,
		ContentSave,
		DatabaseEyeOutline,
		DatabaseCogOutline,
		LayersSearchOutline,
		LayersOutline,
		FileTreeOutline,
		Finance,
	},
	data() {
		return {
			// all of this is settings and should be moved
			settingsOpen: false,
			orc_location: '',
			orc_key: '',
			drc_location: '',
			drc_key: '',
			elastic_location: '',
			elastic_key: '',
			loading: true,
			organisation_name: '',
			organisation_oin: '',
			organisation_pki: '',
			catalogi: [],
			configuration: {
				drcLocation: '',
				drcKey: '',
				orcLocation: '',
				orcKey: '',
				elasticLocation: '',
				elasticKey: '',
				mongodbLocation: '',
				mongodbKey: '',
				mongodbCluster: '',
				organisationName: '',
				organisationOin: '',
				organisationPki: '',
			},
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		// We use the catalogi in the menu so lets fetch those
		fetchData(newPage) {
			this.loading = true
			// Catalogi details
			fetch(
				'/index.php/apps/zaakafhandelapp/catalogi/api',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.catalogi = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})

			fetch(
				'/index.php/apps/zaakafhandelapp/configuration',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.configuration = data
					})
				})
				.catch((err) => {
					console.error(err)
				})
		},
		saveConfig() {
			 // Simple POST request with a JSON body using fetch
			const requestOptions = {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(this.configuration),
			}

			fetch('/index.php/apps/talog/configuration', requestOptions)
				.then((response) => {
					response.json().then((data) => {
						this.configuration = data
					})
				})
				.catch((err) => {
					console.error(err)
				})
		},
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
