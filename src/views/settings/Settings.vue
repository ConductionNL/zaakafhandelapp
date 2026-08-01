<template>
	<CnAdminSettingsShell
		app-id="zaakafhandelapp"
		app-name="Zaak afhandelapp"
		doc-url="https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers"
		:show-reimport="false">
		<NcSettingsSection :name="t('zaakafhandelapp', 'Data storage')" :description="t('zaakafhandelapp', 'Configure where data is stored: in the Nextcloud database or open registers, including external storage like mongodb')">
			<div v-if="!loading">
				<div v-if="!openRegisterInstalled">
					<NcNoteCard type="info">
						{{ t('zaakafhandelapp', 'You have not yet installed open registers, we recommend that you do so.') }}
					</NcNoteCard>

					<NcButton
						variant="primary"
						@click="openLink('/index.php/settings/apps/organization/openregister', '_blank')">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-if="!loading && !saving" :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Install open registers') }}
					</NcButton>
				</div>

				<div v-if="!openRegisterInstalled && (settingsData.berichten_source === 'openregister' || settingsData.besluiten_source === 'openregister' || settingsData.documenten_source === 'openregister' || settingsData.klanten_source === 'openregister' || settingsData.resultaten_source === 'openregister' || settingsData.taken_source === 'openregister' || settingsData.informatieobjecten_source === 'openregister' || settingsData.organisaties_source === 'openregister' || settingsData.personen_source === 'openregister' || settingsData.zaken_source === 'openregister' || settingsData.contactmomenten_source === 'openregister' || settingsData.medewerkers_source === 'openregister' || settingsData.rollen_source === 'openregister')">
					<NcNoteCard type="warning">
						{{ t('zaakafhandelapp', 'It looks like you have selected an open register but it is not yet installed. this may cause problems. would you like to reset the setting?') }}
					</NcNoteCard>
					<NcButton
						variant="primary"
						@click="resetConfig()">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-if="!loading && !saving" :size="20" />
						</template>
						{{ t('zaakafhandelapp', 'Reset') }}
					</NcButton>
				</div>

				<div v-for="objectType in translatedObjectTypesList" :key="objectType.id">
					<h3>{{ objectType.title }}</h3>
					<p>{{ objectType.description }}</p>
					<NcButton v-if="objectType.helpLink" @click="openLink(objectType.helpLink, '_blank')">
						{{ t('zaakafhandelapp', 'More information') }}
					</NcButton>
					<div class="selectionContainer">
						<NcSelect v-bind="labelOptions"
							v-model="getDataProperty(objectType.id).selectedSource"
							required
							:input-label="t('zaakafhandelapp', 'Source')"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcSelect v-if="getDataProperty(objectType.id).selectedSource?.value === 'openregister'"
							v-bind="availableRegistersOptions"
							v-model="getDataProperty(objectType.id).selectedRegister"
							:input-label="t('zaakafhandelapp', 'Register')"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcSelect v-if="getDataProperty(objectType.id).selectedSource?.value === 'openregister' && getDataProperty(objectType.id).selectedRegister?.value"
							v-bind="getDataProperty(objectType.id).availableSchemas"
							v-model="getDataProperty(objectType.id).selectedSchema"
							:input-label="t('zaakafhandelapp', 'Schema')"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcButton
							variant="primary"
							:disabled="loading || saving || getDataProperty(objectType.id).loading || !getDataProperty(objectType.id).selectedSource?.value || getDataProperty(objectType.id).selectedSource?.value === 'openregister' && (!getDataProperty(objectType.id).selectedRegister?.value || !getDataProperty(objectType.id).selectedSchema?.value)"
							@click="saveConfig(objectType.id)">
							<template #icon>
								<NcLoadingIcon v-if="loading || getDataProperty(objectType.id).loading" :size="20" />
								<Plus v-if="!loading && !getDataProperty(objectType.id).loading" :size="20" />
							</template>
							{{ t('zaakafhandelapp', 'Save') }}
						</NcButton>
					</div>
				</div>
				<NcButton
					variant="primary"
					:disabled="saving"
					@click="saveAll()">
					<template #icon>
						<NcLoadingIcon v-if="saving" :size="20" />
						<Plus v-if="!saving" :size="20" />
					</template>
					{{ t('zaakafhandelapp', 'Save all') }}
				</NcButton>
			</div>
			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				:name="t('zaakafhandelapp', 'Loading settings')" />
		</NcSettingsSection>
	</CnAdminSettingsShell>
</template>

<script>
// Components
import { NcSettingsSection, NcNoteCard, NcSelect, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import { CnAdminSettingsShell } from '@conduction/nextcloud-vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Restart from 'vue-material-design-icons/Restart.vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'

export default {
	name: 'Settings',
	components: {
		CnAdminSettingsShell,
		NcSettingsSection,
		NcNoteCard,
		NcSelect,
		NcButton,
		NcLoadingIcon,
		Plus,
		Restart,
	},
	data() {
		return {
			loading: false,
			openRegisterInstalled: false,
			initialization: false,
			saving: false,
			settingsData: {},
			availableRegisters: [],
			availableRegistersOptions: [],
			objectTypes: [],
			berichten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			besluiten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			documenten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			klanten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			resultaten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			taken: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			informatieobjecten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			organisaties: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			personen: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			zaken: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			zaaktypen: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			contactmomenten: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			medewerkers: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			rollen: {
				selectedSource: '',
				selectedRegister: '',
				selectedSchema: '',
				availableSchemas: [],
				loading: false,
			},
			labelOptions: {
				options: [
					{ label: 'Internal', value: 'internal' },
					{ label: 'OpenRegister', value: 'openregister' },
				],
			},
			objectTypesList: [],
		}
	},

	computed: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		translatedObjectTypesList() {
			return [
				{ id: 'berichten', title: t('zaakafhandelapp', 'Messages'), description: t('zaakafhandelapp', 'Configure storage for messages'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'besluiten', title: t('zaakafhandelapp', 'Decisions'), description: t('zaakafhandelapp', 'Configure storage for decisions'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'documenten', title: t('zaakafhandelapp', 'Documents'), description: t('zaakafhandelapp', 'Configure storage for documents'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'klanten', title: t('zaakafhandelapp', 'Customers'), description: t('zaakafhandelapp', 'Configure storage for customer data'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'resultaten', title: t('zaakafhandelapp', 'Results'), description: t('zaakafhandelapp', 'Configure storage for results'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'taken', title: t('zaakafhandelapp', 'Tasks'), description: t('zaakafhandelapp', 'Configure storage for tasks'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'informatieobjecten', title: t('zaakafhandelapp', 'Information objects'), description: t('zaakafhandelapp', 'Configure storage for information objects'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'organisaties', title: t('zaakafhandelapp', 'Organisations'), description: t('zaakafhandelapp', 'Configure storage for organisation data'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'personen', title: t('zaakafhandelapp', 'Persons'), description: t('zaakafhandelapp', 'Configure storage for person data'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'zaken', title: t('zaakafhandelapp', 'Cases'), description: t('zaakafhandelapp', 'Configure storage for cases'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'zaaktypen', title: t('zaakafhandelapp', 'Case types'), description: t('zaakafhandelapp', 'Configure storage for case types'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'contactmomenten', title: t('zaakafhandelapp', 'Contact moments'), description: t('zaakafhandelapp', 'Configure storage for contact moments'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'medewerkers', title: t('zaakafhandelapp', 'Employees'), description: t('zaakafhandelapp', 'Configure storage for employees'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
				{ id: 'rollen', title: t('zaakafhandelapp', 'Roles'), description: t('zaakafhandelapp', 'Configure storage for roles'), helpLink: 'https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers' },
			]
		},
	},

	watch: {

		'berichten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.berichten.selectedRegister = ''
					this.berichten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'berichten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'berichten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.berichten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'besluiten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.besluiten.selectedRegister = ''
					this.besluiten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'besluiten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'besluiten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.besluiten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'documenten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.documenten.selectedRegister = ''
					this.documenten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'documenten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'documenten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.documenten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'klanten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.klanten.selectedRegister = ''
					this.klanten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'klanten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'klanten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.klanten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'resultaten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.resultaten.selectedRegister = ''
					this.resultaten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'resultaten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'resultaten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.resultaten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'taken.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.taken.selectedRegister = ''
					this.taken.selectedSchema = ''
				}
			},
			deep: true,
		},
		'taken.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'taken')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.taken.selectedSchema = '')
				}
			},
			deep: true,
		},
		'informatieobjecten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.informatieobjecten.selectedRegister = ''
					this.informatieobjecten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'informatieobjecten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'informatieobjecten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.informatieobjecten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'organisaties.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.organisaties.selectedRegister = ''
					this.organisaties.selectedSchema = ''
				}
			},
			deep: true,
		},
		'organisaties.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'organisaties')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.organisaties.selectedSchema = '')
				}
			},
			deep: true,
		},
		'personen.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.personen.selectedRegister = ''
					this.personen.selectedSchema = ''
				}
			},
			deep: true,
		},
		'personen.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'personen')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.personen.selectedSchema = '')
				}
			},
			deep: true,
		},
		'zaken.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.zaken.selectedRegister = ''
					this.zaken.selectedSchema = ''
				}
			},
			deep: true,
		},
		'zaken.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'zaken')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.zaken.selectedSchema = '')
				}
			},
			deep: true,
		},
		'zaaktypen.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.zaaktypen.selectedRegister = ''
					this.zaaktypen.selectedSchema = ''
				}
			},
			deep: true,
		},
		'zaaktypen.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'zaaktypen')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.zaaktypen.selectedSchema = '')
				}
			},
			deep: true,
		},
		'contactmomenten.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.contactmomenten.selectedRegister = ''
					this.contactmomenten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'contactmomenten.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'contactmomenten')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.contactmomenten.selectedSchema = '')
				}
			},
			deep: true,
		},
		'medewerkers.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.medewerkers.selectedRegister = ''
					this.medewerkers.selectedSchema = ''
				}
			},
			deep: true,
		},
		'medewerkers.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'medewerkers')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.medewerkers.selectedSchema = '')
				}
			},
			deep: true,
		},
		'rollen.selectedSource': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue) {
				if (newValue?.value === 'internal') {
					this.rollen.selectedRegister = ''
					this.rollen.selectedSchema = ''
				}
			},
			deep: true,
		},
		'rollen.selectedRegister': {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(newValue, oldValue) {
				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'rollen')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.rollen.selectedSchema = '')
				}
			},
			deep: true,
		},

	},
	mounted() {
		this.fetchAll()
	},
	methods: {
		t,
		n,
		getDataProperty(name) {
			return this[name]

		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		setRegisterSchemaOptions(registerId, property) {
			const selectedRegister = this.availableRegisters.find((register) => register.id.toString() === registerId)

			this[property].availableSchemas = {
				options: selectedRegister?.schemas?.map((schema) => ({
					value: schema.id.toString(),
					label: schema.title,
				})),
			}
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-001
		 */
		fetchAll() {
			this.loading = true

			fetch('/index.php/apps/zaakafhandelapp/settings',
				{
					method: 'GET',
				},
			)
				.then((response) => {
					this.initialization = true
					response.json().then((data) => {
						this.openRegisterInstalled = data.openRegisters
						this.settingsData = data
						this.availableRegisters = data.availableRegisters
						this.objectTypes = data.objectTypes

						this.availableRegistersOptions = {
							options: data.availableRegisters.map((register) => ({
								value: register.id.toString(),
								label: register.title,
							})),
						}

						data.objectTypes.forEach((objectType) => {

							if (data[objectType + '_register']) {
								this.setRegisterSchemaOptions(data[objectType + '_register'], objectType)
							}

							this[objectType] = {
								// `===` binds tighter than `??`, so this parsed as
								// `(option.value === data[x]) ?? data[x]` — a boolean is
								// never nullish, so the right-hand side was dead code.
								selectedSource: this.labelOptions.options.find((option) => option.value === data[objectType + '_source']),
								selectedRegister: this.availableRegistersOptions.options.find((option) => option.value === data[objectType + '_register']),
								selectedSchema: this[objectType]?.availableSchemas?.options?.find((option) => option.value === data[objectType + '_schema']),
							}
						})

						this.initialization = false
						this.loading = false
					})
				})
				.catch((err) => {
					console.error(err)
					this.initialization = false
					this.loading = false
					return err
				})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */
		saveConfig(configId) {
			this[configId].loading = true
			this.saving = true
			console.info(`Saving ${configId} config`)

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			fetch('/index.php/apps/zaakafhandelapp/settings',
				{
					method: 'POST',
					body: JSON.stringify({
						...settingsDataCopy,
						[configId + '_register']: this[configId].selectedRegister?.value ?? '',
						[configId + '_schema']: this[configId].selectedSchema?.value ?? '',
						[configId + '_source']: this[configId].selectedSource?.value ?? 'internal',
					}),
					headers: {
						'Content-Type': 'application/json',
					},
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this[configId].loading = false
						this.saving = false

						this.settingsData = {
							...this.settingsData,
							[configId + '_register']: data[configId + '_register'],
							[configId + '_schema']: data[configId + '_schema'],
							[configId + '_source']: data[configId + '_source'],
						}
					})
				})
				.catch((err) => {
					console.error(err)
					this[configId].loading = false
					this.saving = false
					return err
				})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */

		saveAll() {
			this.saving = true
			this.objectTypes.forEach((objectType) => {
				this[objectType].loading = true
			})

			console.info('Saving all config')

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			fetch('/index.php/apps/zaakafhandelapp/settings',
				{
					method: 'POST',
					body: JSON.stringify({
						...settingsDataCopy,
						berichten_register: this.berichten.selectedRegister?.value ?? '',
						berichten_schema: this.berichten.selectedSchema?.value ?? '',
						berichten_source: this.berichten.selectedSource?.value ?? 'internal',
						besluiten_register: this.besluiten.selectedRegister?.value ?? '',
						besluiten_schema: this.besluiten.selectedSchema?.value ?? '',
						besluiten_source: this.besluiten.selectedSource?.value ?? 'internal',
						documenten_register: this.documenten.selectedRegister?.value ?? '',
						documenten_schema: this.documenten.selectedSchema?.value ?? '',
						documenten_source: this.documenten.selectedSource?.value ?? 'internal',
						klanten_register: this.klanten.selectedRegister?.value ?? '',
						klanten_schema: this.klanten.selectedSchema?.value ?? '',
						klanten_source: this.klanten.selectedSource?.value ?? 'internal',
						resultaten_register: this.resultaten.selectedRegister?.value ?? '',
						resultaten_schema: this.resultaten.selectedSchema?.value ?? '',
						resultaten_source: this.resultaten.selectedSource?.value ?? 'internal',
						taken_register: this.taken.selectedRegister?.value ?? '',
						taken_schema: this.taken.selectedSchema?.value ?? '',
						taken_source: this.taken.selectedSource?.value ?? 'internal',
						informatieobjecten_register: this.informatieobjecten.selectedRegister?.value ?? '',
						informatieobjecten_schema: this.informatieobjecten.selectedSchema?.value ?? '',
						informatieobjecten_source: this.informatieobjecten.selectedSource?.value ?? 'internal',
						organisaties_register: this.organisaties.selectedRegister?.value ?? '',
						organisaties_schema: this.organisaties.selectedSchema?.value ?? '',
						organisaties_source: this.organisaties.selectedSource?.value ?? 'internal',
						personen_register: this.personen.selectedRegister?.value ?? '',
						personen_schema: this.personen.selectedSchema?.value ?? '',
						personen_source: this.personen.selectedSource?.value ?? 'internal',
						zaken_register: this.zaken.selectedRegister?.value ?? '',
						zaken_schema: this.zaken.selectedSchema?.value ?? '',
						zaken_source: this.zaken.selectedSource?.value ?? 'internal',
						zaaktypen_register: this.zaaktypen.selectedRegister?.value ?? '',
						zaaktypen_schema: this.zaaktypen.selectedSchema?.value ?? '',
						zaaktypen_source: this.zaaktypen.selectedSource?.value ?? 'internal',
						contactmomenten_register: this.contactmomenten.selectedRegister?.value ?? '',
						contactmomenten_schema: this.contactmomenten.selectedSchema?.value ?? '',
						contactmomenten_source: this.contactmomenten.selectedSource?.value ?? 'internal',
						medewerkers_register: this.medewerkers.selectedRegister?.value ?? '',
						medewerkers_schema: this.medewerkers.selectedSchema?.value ?? '',
						medewerkers_source: this.medewerkers.selectedSource?.value ?? 'internal',
						rollen_register: this.rollen.selectedRegister?.value ?? '',
						rollen_schema: this.rollen.selectedSchema?.value ?? '',
						rollen_source: this.rollen.selectedSource?.value ?? 'internal',
					}),
					headers: {
						'Content-Type': 'application/json',
					},
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.saving = false
						this.objectTypes.forEach((objectType) => {
							this[objectType].loading = false
						})
						this.settingsData = {
							...this.settingsData,
							berichten_register: data.berichten_register,
							berichten_schema: data.berichten_schema,
							berichten_source: data.berichten_source,
							besluiten_register: data.besluiten_register,
							besluiten_schema: data.besluiten_schema,
							besluiten_source: data.besluiten_source,
							documenten_register: data.documenten_register,
							documenten_schema: data.documenten_schema,
							documenten_source: data.documenten_source,
							klanten_register: data.klanten_register,
							klanten_schema: data.klanten_schema,
							klanten_source: data.klanten_source,
							resultaten_register: data.resultaten_register,
							resultaten_schema: data.resultaten_schema,
							resultaten_source: data.resultaten_source,
							taken_register: data.taken_register,
							taken_schema: data.taken_schema,
							taken_source: data.taken_source,
							informatieobjecten_register: data.informatieobjecten_register,
							informatieobjecten_schema: data.informatieobjecten_schema,
							informatieobjecten_source: data.informatieobjecten_source,
							organisaties_register: data.organisaties_register,
							organisaties_schema: data.organisaties_schema,
							organisaties_source: data.organisaties_source,
							personen_register: data.personen_register,
							personen_schema: data.personen_schema,
							personen_source: data.personen_source,
							zaken_register: data.zaken_register,
							zaken_schema: data.zaken_schema,
							zaken_source: data.zaken_source,
							zaaktypen_register: data.zaaktypen_register,
							zaaktypen_schema: data.zaaktypen_schema,
							zaaktypen_source: data.zaaktypen_source,
							contactmomenten_register: data.contactmomenten_register,
							contactmomenten_schema: data.contactmomenten_schema,
							contactmomenten_source: data.contactmomenten_source,
							medewerkers_register: data.medewerkers_register,
							medewerkers_schema: data.medewerkers_schema,
							medewerkers_source: data.medewerkers_source,
							rollen_register: data.rollen_register,
							rollen_schema: data.rollen_schema,
							rollen_source: data.rollen_source,
						}

					})
				})
				.catch((err) => {
					console.error(err)
					this.saving = false
					this.objectTypes.forEach((objectType) => {
						this[objectType].loading = false
					})
					return err
				})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
		 */

		resetConfig() {
			this.saving = true

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			fetch('/index.php/apps/zaakafhandelapp/settings',
				{
					method: 'POST',
					body: JSON.stringify({
						...settingsDataCopy,
						berichten_register: '',
						berichten_schema: '',
						berichten_source: 'internal',
						besluiten_register: '',
						besluiten_schema: '',
						besluiten_source: 'internal',
						documenten_register: '',
						documenten_schema: '',
						documenten_source: 'internal',
						klanten_register: '',
						klanten_schema: '',
						klanten_source: 'internal',
						resultaten_register: '',
						resultaten_schema: '',
						resultaten_source: 'internal',
						taken_register: '',
						taken_schema: '',
						taken_source: 'internal',
						informatieobjecten_register: '',
						informatieobjecten_schema: '',
						informatieobjecten_source: 'internal',
						organisaties_register: '',
						organisaties_schema: '',
						organisaties_source: 'internal',
						personen_register: '',
						personen_schema: '',
						personen_source: 'internal',
						zaken_register: '',
						zaken_schema: '',
						zaken_source: 'internal',
						zaaktypen_register: '',
						zaaktypen_schema: '',
						zaaktypen_source: 'internal',
						contactmomenten_register: '',
						contactmomenten_schema: '',
						contactmomenten_source: 'internal',
						medewerkers_register: '',
						medewerkers_schema: '',
						medewerkers_source: 'internal',
						rollen_register: '',
						rollen_schema: '',
						rollen_source: 'internal',
					}),
					headers: {
						'Content-Type': 'application/json',
					},
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.saving = false
						this.fetchAll()
					})
				})
				.catch((err) => {
					console.error(err)
					this.saving = false
					return err
				})
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-004
		 */
		openLink(url, type = '') {
			window.open(url, type)
		},
	},
}
</script>
<style>
.selectionContainer {
	display: grid;
	grid-gap: 5px;
	grid-template-columns: 1fr;
}

.selectionContainer > * {
	margin-block-end: 10px;
}
</style>
