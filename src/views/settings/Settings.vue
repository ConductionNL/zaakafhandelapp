<template>
	<div>
		<NcSettingsSection :name="'Zaak Afhandelapp'" description="Eén centrale plek voor zaakafhandeling binnen de overheid" doc-url="https://conduction.gitbook.io/zaakafhandelapp-nextcloud/gebruikers" />
		<NcSettingsSection :name="'Data storage'" description="Korte uitleg over dat je kan opslaan in de nextcloud database of open registers en via open registers ook in externe opslag zo al mongo db">
			<div v-if="!loading">
				<div v-if="!openRegisterInstalled">
					<NcNoteCard type="info">
						Je hebt nog geen Open Registers geïnstalleerd, we raden je aan om dat wel te doen.
					</NcNoteCard>

					<NcButton
						type="primary"
						@click="openLink('/index.php/settings/apps/organization/openregister', '_blank')">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-if="!loading && !saving" :size="20" />
						</template>
						Installeer Open Registers
					</NcButton>
				</div>

				<div v-if="!openRegisterInstalled && (settingsData.berichten_source === 'openregister' || settingsData.besluiten_source === 'openregister' || settingsData.documenten_source === 'openregister' || settingsData.klanten_source === 'openregister' || settingsData.resultaten_source === 'openregister' || settingsData.taken_source === 'openregister' || settingsData.informatieobjecten_source === 'openregister' || settingsData.organisaties_source === 'openregister' || settingsData.personen_source === 'openregister' || settingsData.zaken_source === 'openregister' || settingsData.contactmomenten_source === 'openregister' || settingsData.medewerkers_source === 'openregister')">
					<NcNoteCard type="warning">
						Het lijkt erop dat je een open register hebt geselecteerd maar dat deze nog niet geïnstalleerd is. Dit kan problemen geven. Wil je de instelling resetten?
					</NcNoteCard>
					<NcButton
						type="primary"
						@click="resetConfig()">
						<template #icon>
							<NcLoadingIcon v-if="loading || saving" :size="20" />
							<Restart v-if="!loading && !saving" :size="20" />
						</template>
						Reset
					</NcButton>
				</div>

				<div v-for="objectType in objectTypesList" :key="objectType.id">
					<h3>{{ objectType.title }}</h3>
					<p>{{ objectType.description }}</p>
					<NcButton v-if="objectType.helpLink" @click="openLink(objectType.helpLink, '_blank')">
						Meer informatie
					</NcButton>
					<div class="selectionContainer">
						<NcSelect v-bind="labelOptions"
							v-model="getDataProperty(objectType.id).selectedSource"
							required
							input-label="Source"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcSelect v-if="getDataProperty(objectType.id).selectedSource?.value === 'openregister'"
							v-bind="availableRegistersOptions"
							v-model="getDataProperty(objectType.id).selectedRegister"
							input-label="Register"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcSelect v-if="getDataProperty(objectType.id).selectedSource?.value === 'openregister' && getDataProperty(objectType.id).selectedRegister?.value"
							v-bind="getDataProperty(objectType.id).availableSchemas"
							v-model="getDataProperty(objectType.id).selectedSchema"
							input-label="Schema"
							:loading="getDataProperty(objectType.id).loading"
							:disabled="loading || getDataProperty(objectType.id).loading" />

						<NcButton
							type="primary"
							:disabled="loading || saving || getDataProperty(objectType.id).loading || !getDataProperty(objectType.id).selectedSource?.value || getDataProperty(objectType.id).selectedSource?.value === 'openregister' && (!getDataProperty(objectType.id).selectedRegister?.value || !getDataProperty(objectType.id).selectedSchema?.value)"
							@click="saveConfig(objectType.id)">
							<template #icon>
								<NcLoadingIcon v-if="loading || getDataProperty(objectType.id).loading" :size="20" />
								<Plus v-if="!loading && !getDataProperty(objectType.id).loading" :size="20" />
							</template>
							Opslaan
						</NcButton>
					</div>
				</div>
				<NcButton
					type="primary"
					:disabled="saving"
					@click="saveAll()">
					<template #icon>
						<NcLoadingIcon v-if="saving" :size="20" />
						<Plus v-if="!saving" :size="20" />
					</template>
					Alles opslaan
				</NcButton>
			</div>
			<NcLoadingIcon v-if="loading"
				class="loadingIcon"
				:size="64"
				appearance="dark"
				name="Settings aan het laden" />
		</NcSettingsSection>
	</div>
</template>

<script>
// Components
import { NcSettingsSection, NcNoteCard, NcSelect, NcButton, NcLoadingIcon } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Restart from 'vue-material-design-icons/Restart.vue'

export default {
	name: 'Settings',
	components: {
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
			labelOptions: {
				options: [
					{ label: 'Internal', value: 'internal' },
					{ label: 'OpenRegister', value: 'openregister' },
				],
			},
			objectTypesList: [
				{ id: 'berichten', title: 'Berichten', description: 'Configureer de opslag voor berichten', helpLink: 'https://example.com/help/berichten' },
				{ id: 'besluiten', title: 'Besluiten', description: 'Configureer de opslag voor besluiten', helpLink: 'https://example.com/help/besluiten' },
				{ id: 'documenten', title: 'Documenten', description: 'Configureer de opslag voor documenten', helpLink: 'https://example.com/help/documenten' },
				{ id: 'klanten', title: 'Klanten', description: 'Configureer de opslag voor klantgegevens', helpLink: 'https://example.com/help/klanten' },
				{ id: 'resultaten', title: 'Resultaten', description: 'Configureer de opslag voor resultaten', helpLink: 'https://example.com/help/resultaten' },
				{ id: 'taken', title: 'Taken', description: 'Configureer de opslag voor taken', helpLink: 'https://example.com/help/taken' },
				{ id: 'informatieobjecten', title: 'Informatieobjecten', description: 'Configureer de opslag voor informatieobjecten', helpLink: 'https://example.com/help/informatieobjecten' },
				{ id: 'organisaties', title: 'Organisaties', description: 'Configureer de opslag voor organisatiegegevens', helpLink: 'https://example.com/help/organisaties' },
				{ id: 'personen', title: 'Personen', description: 'Configureer de opslag voor persoonsgegevens', helpLink: 'https://example.com/help/personen' },
				{ id: 'zaken', title: 'Zaken', description: 'Configureer de opslag voor zaken', helpLink: 'https://example.com/help/zaken' },
				{ id: 'zaaktypen', title: 'Zaaktypen', description: 'Configureer de opslag voor zaaktypen', helpLink: 'https://example.com/help/zaaktypen' },
				{ id: 'contactmomenten', title: 'Contactmomenten', description: 'Configureer de opslag voor contactmomenten', helpLink: 'https://example.com/help/contactmomenten' },
				{ id: 'medewerkers', title: 'Medewerkers', description: 'Configureer de opslag voor medewerkers', helpLink: 'https://example.com/help/medewerkers' },
			],
		}
	},

	watch: {

		'berichten.selectedSource': {
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.berichten.selectedRegister = ''
					this.berichten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'berichten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.besluiten.selectedRegister = ''
					this.besluiten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'besluiten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.documenten.selectedRegister = ''
					this.documenten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'documenten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.klanten.selectedRegister = ''
					this.klanten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'klanten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.resultaten.selectedRegister = ''
					this.resultaten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'resultaten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.taken.selectedRegister = ''
					this.taken.selectedSchema = ''
				}
			},
			deep: true,
		},
		'taken.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.informatieobjecten.selectedRegister = ''
					this.informatieobjecten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'informatieobjecten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.organisaties.selectedRegister = ''
					this.organisaties.selectedSchema = ''
				}
			},
			deep: true,
		},
		'organisaties.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.personen.selectedRegister = ''
					this.personen.selectedSchema = ''
				}
			},
			deep: true,
		},
		'personen.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.zaken.selectedRegister = ''
					this.zaken.selectedSchema = ''
				}
			},
			deep: true,
		},
		'zaken.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.zaaktypen.selectedRegister = ''
					this.zaaktypen.selectedSchema = ''
				}
			},
			deep: true,
		},
		'zaaktypen.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.contactmomenten.selectedRegister = ''
					this.contactmomenten.selectedSchema = ''
				}
			},
			deep: true,
		},
		'contactmomenten.selectedRegister': {
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
			handler(newValue) {
				if (newValue?.value === 'internal') {

					this.medewerkers.selectedRegister = ''
					this.medewerkers.selectedSchema = ''
				}
			},
			deep: true,
		},
		'medewerkers.selectedRegister': {
			handler(newValue, oldValue) {

				if (this.initialization === true && oldValue === '') return
				if (newValue) {
					this.setRegisterSchemaOptions(newValue?.value, 'medewerkers')
					oldValue !== '' && newValue?.value !== oldValue.value && (this.medewerkers.selectedSchema = '')
				}
			},
			deep: true,
		},

	},
	mounted() {
		this.fetchAll()
	},
	methods: {
		getDataProperty(name) {
			return this[name]

		},
		setRegisterSchemaOptions(registerId, property) {
			const selectedRegister = this.availableRegisters.find((register) => register.id.toString() === registerId)

			this[property].availableSchemas = {
				options: selectedRegister?.schemas?.map((schema) => ({
					value: schema.id.toString(),
					label: schema.title,
				})),
			}
		},
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
								selectedSource: this.labelOptions.options.find((option) => option.value === data[objectType + '_source'] ?? data[objectType + '_source']),
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
						contactmomenten_register: this.contactmomenten.selectedRegister?.value ?? '',
						contactmomenten_schema: this.contactmomenten.selectedSchema?.value ?? '',
						contactmomenten_source: this.contactmomenten.selectedSource?.value ?? 'internal',
						medewerkers_register: this.medewerkers.selectedRegister?.value ?? '',
						medewerkers_schema: this.medewerkers.selectedSchema?.value ?? '',
						medewerkers_source: this.medewerkers.selectedSource?.value ?? 'internal',
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
							contactmomenten_register: data.contactmomenten_register,
							contactmomenten_schema: data.contactmomenten_schema,
							contactmomenten_source: data.contactmomenten_source,
							medewerkers_register: data.medewerkers_register,
							medewerkers_schema: data.medewerkers_schema,
							medewerkers_source: data.medewerkers_source,
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

		resetConfig() {
			this.saving = true

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			fetch('/index.php/apps/opencatalogi/settings',
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
						contactmomenten_register: '',
						contactmomenten_schema: '',
						contactmomenten_source: 'internal',
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
