<template>
	<div>
		<NcAppNavigationItem name="Configuration" @click="settingsOpen = true">
			<template #icon>
				<CogOutline :size="20" />
			</template>
		</NcAppNavigationItem>
		<NcAppSettingsDialog :open.sync="settingsOpen" :show-navigation="true" name="Application settings">
			<NcAppSettingsSection id="storage" name="Storage" doc-url="zaakafhandel.app">
				<template #icon>
					<Database :size="20" />
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
			<NcAppSettingsSection id="connections" name="Connections" doc-url="zaakafhandel.app">
				<template #icon>
					<Connection :size="20" />
				</template>
				<div class="wrapper">
					<b>Klanten API</b>
					<NcTextField :value.sync="configuration.klantenLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.klantenLocation !== ''"
						@trailing-button-click="configuration.klantenLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.klantenKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.klantenKey !== ''"
						@trailing-button-click="configuration.klantenKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Berichten API</b>
					<NcTextField :value.sync="configuration.berichtenLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.berichtenLocation !== ''"
						@trailing-button-click="configuration.berichtenLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.berichtenKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.berichtenKey !== ''"
						@trailing-button-click="configuration.berichtenKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Taken API</b>
					<NcTextField :value.sync="configuration.takenLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.takenLocation !== ''"
						@trailing-button-click="configuration.takenLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.takenKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.takenKey !== ''"
						@trailing-button-click="configuration.takenKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Zaken Register</b>
					<NcTextField :value.sync="configuration.zrcLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcLocation !== ''"
						@trailing-button-click="configuration.zrcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.zrcKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcKey !== ''"
						@trailing-button-click="configuration.zrcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Objecten Register</b>
					<NcTextField :value.sync="configuration.orcLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.orcLocation !== ''"
						@trailing-button-click="configuration.orcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.zrcKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcKey !== ''"
						@trailing-button-click="configuration.zrcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Documenten Register</b>
					<NcTextField :value.sync="configuration.drcLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.drcLocation !== ''"
						@trailing-button-click="configuration.drcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.zrcKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcKey !== ''"
						@trailing-button-click="configuration.zrcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Besluiten Register</b>
					<NcTextField :value.sync="configuration.brcLocation"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.brcLocation !== ''"
						@trailing-button-click="configuration.brcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="configuration.brcKey"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="configuration.brcKey !== ''"
						@trailing-button-click="configuration.brcKey = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
			</NcAppSettingsSection>
			<NcAppSettingsSection id="organisation" name="Organisation" doc-url="zaakafhandel.app">
				<template #icon>
					<OfficeBuildingOutline :size="20" />
				</template>
				<div class="wrapper">
					<NcTextField :value.sync="configuration.organisationName"
						label="The name of your organisation"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationName !== ''"
						@trailing-button-click="configuration.organisationName = ''" />
					<NcTextField :value.sync="configuration.organisationOIN"
						label="The oin of your organisation"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationOIN !== ''"
						@trailing-button-click="configuration.organisationOIN = ''" />
					<NcTextField :value.sync="configuration.organisationRSIN"
						label="The rsin of your organisation"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationRSIN !== ''"
						@trailing-button-click="configuration.organisationRSIN = ''" />
					<NcTextField :value.sync="configuration.organisationKVK"
						label="The kvk of your organisation"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationKVK !== ''"
						@trailing-button-click="configuration.organisationKVK = ''" />
					<NcTextArea
						:value.sync="configuration.organisationPKI"
						label="A PKI for yout organisation"
						placeholder="Your public PKI certificates here"
						helper-text="PKI certificates are used for connections on the FCS network" />
				</div>
			</NcAppSettingsSection>
		</NcAppSettingsDialog>
	</div>
</template>
<script>

import {
	NcAppSettingsDialog,
	NcAppSettingsSection,
	NcAppNavigationItem,
	NcTextField,
	NcTextArea,
} from '@nextcloud/vue'

import Database from 'vue-material-design-icons/Database.vue'
import Connection from 'vue-material-design-icons/Connection.vue'
import CogOutline from 'vue-material-design-icons/CogOutline.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import Lock from 'vue-material-design-icons/Lock.vue'
import Web from 'vue-material-design-icons/Web.vue'

export default {
	name: 'Configuration',
	components: {
		NcAppSettingsDialog,
		NcAppSettingsSection,
		NcAppNavigationItem,
		NcTextField,
		NcTextArea,
		CogOutline,
		Connection,
		Database,
		Lock,
		Web,
		OfficeBuildingOutline,
	},
	data() {
		return {
			// all of this is settings and should be moved
			settingsOpen: false,
			loading: true,
			catalogi: [],
			configuration: {
				drcLocation: '',
				drcKey: '',
				orcLocation: '',
				orcKey: '',
				zrcLocation: '',
				zrcKey: '',
				brcLocation: '',
				brcKey: '',
				klantenLocation: '',
				klantenKey: '',
				berichtenLocation: '',
				berichtenKey: '',
				takenLocation: '',
				takenKey: '',
				elasticLocation: '',
				elasticKey: '',
				mongodbLocation: '',
				mongodbKey: '',
				mongodbCluster: '',
				organisationName: '',
				organisationOIN: '',
				organisationPKI: '',
				organisationRSIN: '',
				organisationKVK: '',
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
