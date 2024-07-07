<script setup>
import { store } from '../store.js'
</script>

<template><NcAppSettingsDialog :open.sync="settingsOpen" :show-navigation="true" name="Application settings">
	<NcAppSettingsSection id="sharing" name="Connections" doc-url="zaakafhandel.app">
		<template #icon>
			<Connection :size="20" />
		</template>

		<p>
			Here you can set the details for varius Connections
		</p>
		<p>
			<table>
				<tbody>
					<tr>
						<td class="row-name">
							DRC
						</td>
						<td>Location</td>
						<td>
							<NcTextField id="drcLocation"
								:value.sync="configuration.drcLocation"
								:label-outside="true"
								placeholder="https://" />
						</td>
						<td>Key</td>
						<td>
							<NcTextField id="drcKey"
								:value.sync="configuration.drcKey"
								:label-outside="true"
								placeholder="***" />
						</td>
					</tr>
					<tr>
						<td class="row-name">
							ORC
						</td>
						<td>Location</td>
						<td>
							<NcTextField id="orcLocation"
								:value.sync="configuration.orcLocation"
								:label-outside="true"
								placeholder="https://" />
						</td>
						<td>Key</td>
						<td>
							<NcTextField id="orcKey"
								:value.sync="configuration.orcKey"
								:label-outside="true"
								placeholder="***" />
						</td>
					</tr>
					<tr>
						<td class="row-name">
							Elastic
						</td>
						<td>Location</td>
						<td>
							<NcTextField id="elasticLocation"
								:value.sync="configuration.elasticLocation"
								:label-outside="true"
								placeholder="https://" />
						</td>
						<td>Key</td>
						<td>
							<NcTextField id="elasticKey"
								:value.sync="configuration.elasticKey"
								:label-outside="true"
								placeholder="***" />
						</td>
					</tr>
					<tr>
						<td class="row-name">
							Mongo DB
						</td>
						<td>Location</td>
						<td>
							<NcTextField id="mongodbLocation"
								:value.sync="configuration.mongodbLocation"
								:label-outside="true"
								placeholder="https://" />
						</td>
						<td>Key</td>
						<td>
							<NcTextField id="mongodbKey"
								:value.sync="configuration.mongodbKey"
								:label-outside="true"
								placeholder="***" />
						</td>
						<td>Cluster name</td>
						<td>
							<NcTextField id="mongodbCluster"
								:value.sync="configuration.mongodbCluster"
								:label-outside="true"
								placeholder="***" />
						</td>
					</tr>
				</tbody>
			</table>
		</p>
		<NcButton aria-label="Save"
			type="primary"
			wide
			@click="saveConfig()">
			<template #icon>
				<ContentSave :size="20" />
			</template>
			Save
		</NcButton>
	</NcAppSettingsSection>
	<NcAppSettingsSection id="organisation" name="Organisation" doc-url="zaakafhandel.app">
		<template #icon>
			<Connection :size="20" />
		</template>

		<p>
			Here you can set the details for your organisation
		</p>

		<NcTextField id="organisationName" :value.sync="configuration.organisationName" />
		<NcTextField id="organisationOin" :value.sync="configuration.organisationOin" />
		<NcTextArea id="organisationPki" :value.sync="configuration.organisationPki" />

		<NcButton aria-label="Save"
			type="primary"
			wide
			@click="saveConfig()">
			<template #icon>
				<ContentSave :size="20" />
			</template>
			Save
		</NcButton>
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
				'/index.php/apps/opencatalog/catalogi/api',
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
				'/index.php/apps/opencatalog/configuration',
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

			fetch('/index.php/apps/opencatalog/configuration', requestOptions)
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
