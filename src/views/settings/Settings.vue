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

				<div v-if="!openRegisterInstalled && (settingsData.berichten_source === 'openregister' || settingsData.besluiten_source === 'openregister' || settingsData.documenten_source === 'openregister' || settingsData.klanten_source === 'openregister' || settingsData.resultaten_source === 'openregister' || settingsData.taken_source === 'openregister' || settingsData.informatieobjecten_source === 'openregister' || settingsData.organisaties_source === 'openregister' || settingsData.personen_source === 'openregister' || settingsData.themas_source === 'openregister')">
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
							v-model="objectTypes[objectType.id].selectedSource"
							required
							input-label="Source"
							:loading="objectTypes[objectType.id].loading"
							:disabled="loading || objectTypes[objectType.id].loading" />

						<NcSelect v-if="objectTypes[objectType.id].selectedSource?.value === 'openregister'"
							v-bind="availableRegisters"
							v-model="objectTypes[objectType.id].selectedRegister"
							input-label="Register"
							:loading="objectTypes[objectType.id].loading"
							:disabled="loading || objectTypes[objectType.id].loading" />

						<NcSelect v-if="objectTypes[objectType.id].selectedSource?.value === 'openregister' && objectTypes[objectType.id].selectedRegister?.value"
							v-bind="objectTypes[objectType.id].availableSchemas"
							v-model="objectTypes[objectType.id].selectedSchema"
							input-label="Schema"
							:loading="objectTypes[objectType.id].loading"
							:disabled="loading || objectTypes[objectType.id].loading" />

						<NcButton
							type="primary"
							:disabled="loading || saving || objectTypes[objectType.id].loading || !objectTypes[objectType.id].selectedSource?.value || objectTypes[objectType.id].selectedSource?.value === 'openregister' && (!objectTypes[objectType.id].selectedRegister?.value || !objectTypes[objectType.id].selectedSchema?.value)"
							@click="saveConfig(objectType.id)">
							<template #icon>
								<NcLoadingIcon v-if="loading || objectTypes[objectType.id].loading" :size="20" />
								<Plus v-if="!loading && !objectTypes[objectType.id].loading" :size="20" />
							</template>
							Opslaan
						</NcButton>
					</div>
				</div>
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
	name: 'AdminSettings',
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
			objectTypes: {},
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
				{ id: 'themas', title: 'Thema\'s', description: 'Configureer de opslag voor thema\'s', helpLink: 'https://example.com/help/themas' },
			],
		}
	},

	watch: {},
	mounted() {
		this.fetchAll()
	},
	methods: {
		setRegisterSchemaOptions(registerId, property) {
			const selectedRegister = this.settingsData.availableRegisters.find((register) => register.id.toString() === registerId)

			this.objectTypes[property].availableSchemas = {
				options: selectedRegister?.schemas?.map((schema) => ({
					value: schema.id.toString(),
					label: schema.title,
				})),
			}
		},
		fetchAll() {
			this.loading = true

			fetch('/index.php/apps/opencatalogi/settings',
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

						this.availableRegisters = {
							options: data.availableRegisters.map((register) => ({
								value: register.id.toString(),
								label: register.title,
							})),
						}

						this.objectTypesList.forEach((objectType) => {
							if (data[objectType.id + '_register']) {
								this.setRegisterSchemaOptions(data[objectType.id + '_register'], objectType.id)
							}
							this.objectTypes[objectType.id] = {
								selectedSource: this.labelOptions.options.find((option) => option.value === data[objectType.id + '_source'] ?? data[objectType.id + '_source']),
								selectedRegister: this.availableRegisters.options.find((option) => option.value === data[objectType.id + '_register']),
								selectedSchema: this.objectTypes[objectType.id]?.availableSchemas?.options?.find((option) => option.value === data[objectType.id + '_schema']),
								loading: false,
								availableSchemas: [],
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
			this.objectTypes[configId].loading = true
			this.saving = true
			console.log(`Saving ${configId} config`)

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			fetch('/index.php/apps/opencatalogi/settings',
				{
					method: 'POST',
					body: JSON.stringify({
						...settingsDataCopy,
						[configId + '_register']: this.objectTypes[configId].selectedRegister?.value ?? '',
						[configId + '_schema']: this.objectTypes[configId].selectedSchema?.value ?? '',
						[configId + '_source']: this.objectTypes[configId].selectedSource?.value ?? 'internal',
					}),
					headers: {
						'Content-Type': 'application/json',
					},
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.objectTypes[configId].loading = false
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
					this.objectTypes[configId].loading = false
					this.saving = false
					return err
				})
		},
		resetConfig() {
			this.saving = true

			const settingsDataCopy = this.settingsData

			delete settingsDataCopy.objectTypes
			delete settingsDataCopy.openRegisters
			delete settingsDataCopy.availableRegisters

			const resetData = this.objectTypesList.reduce((acc, objectType) => {
				acc[objectType.id + '_source'] = 'internal'
				acc[objectType.id + '_schema'] = ''
				acc[objectType.id + '_register'] = ''
				return acc
			}, {})

			fetch('/index.php/apps/opencatalogi/settings',
				{
					method: 'POST',
					body: JSON.stringify({
						...settingsDataCopy,
						...resetData,
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
