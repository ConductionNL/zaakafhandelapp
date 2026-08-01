<template>
	<div>
		<NcAppNavigationItem :name="t('zaakafhandelapp', 'Configuration')" @click="settingsOpen = true">
			<template #icon>
				<CogOutline :size="20" />
			</template>
		</NcAppNavigationItem>
		<NcAppSettingsDialog v-model:open="settingsOpen" :show-navigation="true" :name="t('zaakafhandelapp', 'Application settings')">
			<NcAppSettingsSection
				v-if="!loading"
				id="storage"
				:name="t('zaakafhandelapp', 'Storage')"
				doc-url="zaakafhandel.app">
				<template #icon>
					<Database :size="20" />
				</template>

				<p>{{ t('zaakafhandelapp', 'The ZaakAfhandelApp allows three types of storage:') }}</p>
			</NcAppSettingsSection>
			<NcAppSettingsSection
				v-if="!loading"
				id="connections"
				:name="t('zaakafhandelapp', 'Connections')"
				doc-url="zaakafhandel.app">
				<template #icon>
					<Connection :size="20" />
				</template>
				<div class="wrapper">
					<NcCheckboxRadioSwitch v-model="configuration.external" type="switch">
						{{ t('forms', 'Enable sharing') }}
					</NcCheckboxRadioSwitch>

					<b>{{ t('zaakafhandelapp', 'Mongo DB') }}</b>
					<NcTextField v-model="configuration.mongodbLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.mongodbLocation !== ''"
						@trailing-button-click="configuration.mongodbLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.mongodbKey"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.mongodbKey !== ''"
						@trailing-button-click="configuration.mongodbKey = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.mongodbCluster"
						:label="t('zaakafhandelapp', 'The cluster')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.mongodbCluster !== ''"
						@trailing-button-click="configuration.mongodbCluster = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Customers API') }}</b>
					<NcTextField v-model="configuration.klantenLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.klantenLocation !== ''"
						@trailing-button-click="configuration.klantenLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.klantenKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.klantenKey !== ''"
						@trailing-button-click="configuration.klantenKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.klantenAuthType"
						:label="t('zaakafhandelapp', 'Customer auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.klantenAuthType !== ''"
						@trailing-button-click="configuration.klantenAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Cases register') }}</b>
					<NcTextField v-model="configuration.zrcLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcLocation !== ''"
						@trailing-button-click="configuration.zrcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.zrcKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcKey !== ''"
						@trailing-button-click="configuration.zrcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.zrcAuthType"
						:label="t('zaakafhandelapp', 'Cases register auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.zrcAuthType !== ''"
						@trailing-button-click="configuration.zrcAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Objects register') }}</b>
					<NcTextField v-model="configuration.orcLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.orcLocation !== ''"
						@trailing-button-click="configuration.orcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.orcKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.orcKey !== ''"
						@trailing-button-click="configuration.orcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.orcAuthType"
						:label="t('zaakafhandelapp', 'Objects register auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.orcAuthType !== ''"
						@trailing-button-click="configuration.orcAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Documents register') }}</b>
					<NcTextField v-model="configuration.drcLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.drcLocation !== ''"
						@trailing-button-click="configuration.drcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.drcKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.drcKey !== ''"
						@trailing-button-click="configuration.drcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.drcAuthType"
						:label="t('zaakafhandelapp', 'Documents register auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.drcAuthType !== ''"
						@trailing-button-click="configuration.drcAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Decisions register') }}</b>
					<NcTextField v-model="configuration.brcLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.brcLocation !== ''"
						@trailing-button-click="configuration.brcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.brcKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.brcKey !== ''"
						@trailing-button-click="configuration.brcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.brcAuthType"
						:label="t('zaakafhandelapp', 'Decisions register auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.brcAuthType !== ''"
						@trailing-button-click="configuration.brcAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
				<div class="wrapper">
					<b>{{ t('zaakafhandelapp', 'Case type catalogue') }}</b>
					<NcTextField v-model="configuration.ztcLocation"
						:label="t('zaakafhandelapp', 'The location (URL)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.ztcLocation !== ''"
						@trailing-button-click="configuration.ztcLocation = ''">
						<Web :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.ztcKey"
						:label="t('zaakafhandelapp', 'The credential (auth key)')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.ztcKey !== ''"
						@trailing-button-click="configuration.ztcKey = ''">
						<Lock :size="20" />
					</NcTextField>
					<NcTextField v-model="configuration.ztcAuthType"
						:label="t('zaakafhandelapp', 'Case type catalogue auth type')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.ztcAuthType !== ''"
						@trailing-button-click="configuration.ztcAuthType = ''">
						<Lock :size="20" />
					</NcTextField>
				</div>
			</NcAppSettingsSection>
			<NcAppSettingsSection
				v-if="!loading"
				id="organisation"
				:name="t('zaakafhandelapp', 'Organisation')"
				doc-url="zaakafhandel.app">
				<template #icon>
					<OfficeBuildingOutline :size="20" />
				</template>
				<div class="wrapper">
					<NcTextField v-model="configuration.organisationName"
						:label="t('zaakafhandelapp', 'The name of your organisation')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationName !== ''"
						@trailing-button-click="configuration.organisationName = ''" />
					<NcTextField v-model="configuration.organisationOIN"
						:label="t('zaakafhandelapp', 'The OIN of your organisation')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationOIN !== ''"
						@trailing-button-click="configuration.organisationOIN = ''" />
					<NcTextField v-model="configuration.organisationRSIN"
						:label="t('zaakafhandelapp', 'The RSIN of your organisation')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationRSIN !== ''"
						@trailing-button-click="configuration.organisationRSIN = ''" />
					<NcTextField v-model="configuration.organisationKVK"
						:label="t('zaakafhandelapp', 'The KVK of your organisation')"
						trailing-button-icon="close"
						:show-trailing-button="configuration.organisationKVK !== ''"
						@trailing-button-click="configuration.organisationKVK = ''" />
					<NcTextArea v-model="configuration.organisationPKI"
						:label="t('zaakafhandelapp', 'A PKI for your organisation')"
						:placeholder="t('zaakafhandelapp', 'Your public PKI certificates here')"
						helper-text="PKI certificates are used for connections on the FCS network" />
				</div>
			</NcAppSettingsSection>
			<NcButton
				v-if="!loading"
				:aria-label="t('zaakafhandelapp', 'Save')"
				variant="primary"
				wide
				@click="saveConfig()">
				<template #icon>
					<ContentSave :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Save') }}
			</NcButton>
			<NcLoadingIcon
				v-if="loading"
				:size="100" />
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
	NcButton,
	NcLoadingIcon,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'

import Database from 'vue-material-design-icons/Database.vue'
import Connection from 'vue-material-design-icons/Connection.vue'
import CogOutline from 'vue-material-design-icons/CogOutline.vue'
import OfficeBuildingOutline from 'vue-material-design-icons/OfficeBuildingOutline.vue'
import Lock from 'vue-material-design-icons/Lock.vue'
import Web from 'vue-material-design-icons/Web.vue'
import ContentSave from 'vue-material-design-icons/ContentSave.vue'

export default {
	name: 'Configuration',
	components: {
		NcAppSettingsDialog,
		NcAppSettingsSection,
		NcAppNavigationItem,
		NcLoadingIcon,
		NcTextField,
		NcTextArea,
		NcButton,
		NcCheckboxRadioSwitch,
		// Icons
		CogOutline,
		Connection,
		Database,
		Lock,
		Web,
		OfficeBuildingOutline,
		ContentSave,
	},
	data() {
		return {
			// all of this is settings and should be moved
			settingsOpen: false,
			loading: false,
			configuration: {
				drcLocation: '',
				drcKey: '',
				drcAuthType: '',
				orcLocation: '',
				orcKey: '',
				orcAuthType: '',
				zrcLocation: '',
				zrcKey: '',
				zrcAuthType: '',
				ztcLocation: '',
				ztcKey: '',
				ztcAuthType: '',
				brcLocation: '',
				brcKey: '',
				brcAuthType: '',
				klantenLocation: '',
				klantenKey: '',
				klantenAuthType: '',
				takenLocation: '',
				takenKey: '',
				takenAuthType: '',
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
			klantenAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
			zrcAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
			drcAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
			brcAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
			ztcAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
			orcAuthTypeOptions: {
				options: [
					{

						id: 'none',
						label: 'none',
					},
					{
						id: 'apiKey',
						label: 'API Key',
					},
					{
						id: 'basic',
						label: 'Basic Auth',

					},
				],
			},
		}
	},
	mounted() {
		this.fetchData()
	},
	methods: {
		// We use the catalogi in the menu so lets fetch those
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-002
		 */
		fetchData(newPage) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/configuration',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.configuration = data
						this.loading = false
					})
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
		/**
		 * @spec openspec/specs/ui-search-navigation/spec.md#REQ-002
		 */
		saveConfig() {
			// Simple POST request with a JSON body using fetch
			const requestOptions = {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(this.configuration),
			}
			this.loading = true
			fetch('/index.php/apps/zaakafhandelapp/api/configuration', requestOptions)
				.then((response) => {
					response.json().then((data) => {
						this.configuration = data
						this.loading = false
					})
				})
				.catch((err) => {
					console.error(err)
					this.loading = true
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
