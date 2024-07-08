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
					<NcTextField :value.sync="klanten_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="klanten_location !== ''"
						@trailing-button-click="klanten_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="klanten_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="klanten_key !== ''"
						@trailing-button-click="klanten_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Berichten API</b>
					<NcTextField :value.sync="berichten_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="berichten_location !== ''"
						@trailing-button-click="berichten_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="berichten_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="berichten_key !== ''"
						@trailing-button-click="berichten_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Taken API</b>
					<NcTextField :value.sync="taken_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="taken_location !== ''"
						@trailing-button-click="taken_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="taken_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="taken_key !== ''"
						@trailing-button-click="taken_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Zaken Regiser</b>
					<NcTextField :value.sync="zrc_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="zrc_location !== ''"
						@trailing-button-click="zrc_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="zrc_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="zrc_key !== ''"
						@trailing-button-click="zrc_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Objecten Regiser</b>
					<NcTextField :value.sync="orc_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="orc_location !== ''"
						@trailing-button-click="orc_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="zrc_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="zrc_key !== ''"
						@trailing-button-click="zrc_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Documenten Regiser</b>
					<NcTextField :value.sync="drc_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="drc_location !== ''"
						@trailing-button-click="drc_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="zrc_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="zrc_key !== ''"
						@trailing-button-click="zrc_key = ''">
						<Lock :size="20" />
					</NcTextField>
					<b>Besluiten Regiser</b>
					<NcTextField :value.sync="brc_location"
						label="The location (url)"
						trailing-button-icon="close"
						:show-trailing-button="brc_location !== ''"
						@trailing-button-click="brc_location = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField :value.sync="brc_key"
						label="The credential (auth key)"
						trailing-button-icon="close"
						:show-trailing-button="brc_key !== ''"
						@trailing-button-click="brc_key = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
			</NcAppSettingsSection>
			<NcAppSettingsSection id="organisation" name="Organisation" doc-url="zaakafhandel.app">
				<template #icon>
					<OfficeBuildingOutline :size="20" />
				</template>

				<p>
					Here you can set the details for your organisation
				</p>
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
			zrc_location: '',
			zrc_key: '',
			orc_location: '',
			orc_key: '',
			drc_location: '',
			drc_key: '',
			brc_location: '',
			brc_key: '',
			klanten_location: '',
			klanten_key: '',
			Taken_location: '',
			Taken_key: '',
			Berichten_location: '',
			Berichten_key: '',
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
