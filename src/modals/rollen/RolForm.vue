<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, rolStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcModal ref="modalRef" labelId="rolForm" @close="closeModal">
		<div class="modal__content">
			<h2>
				{{
					IS_EDIT
						? t('zaakafhandelapp', 'Role {action}', {
								action: t('zaakafhandelapp', 'edit'),
							})
						: t('zaakafhandelapp', 'Role {action}', {
								action: t('zaakafhandelapp', 'create'),
							})
				}}
			</h2>

			<NcNoteCard v-if="success" type="success">
				<p>
					{{
						IS_EDIT
							? t('zaakafhandelapp', 'Role successfully {action}', {
									action: t('zaakafhandelapp', 'updated'),
								})
							: t('zaakafhandelapp', 'Role successfully {action}', {
									action: t('zaakafhandelapp', 'created'),
								})
					}}
				</p>
			</NcNoteCard>
			<NcNoteCard v-if="error" type="error">
				<p>{{ error }}</p>
			</NcNoteCard>

			<div v-if="success === null" class="form-group">
				<NcSelect
					v-bind="zaakOptions"
					v-model="zaakOptions.value"
					:disabled="loading"
					:inputLabel="t('zaakafhandelapp', 'Case')"
					required />

				<NcTextField
					v-model="rolItem.betrokkene"
					:disabled="loading"
					maxlength="1000"
					:label="t('zaakafhandelapp', 'Involved party (URL)')" />

				<NcSelect
					v-bind="betrokkeneTypeOptions"
					v-model="betrokkeneTypeOptions.value"
					:disabled="loading"
					:clearable="false"
					:inputLabel="t('zaakafhandelapp', 'Involved party type')"
					required />

				<NcTextField
					v-model="rolItem.afwijkendeNaamBetrokkene"
					:disabled="loading"
					maxlength="625"
					:label="t('zaakafhandelapp', 'Deviating name involved party')" />

				<NcTextField
					v-model="rolItem.roltype"
					:disabled="loading"
					maxlength="1000"
					:label="t('zaakafhandelapp', 'Role type')"
					required />

				<NcTextArea
					v-model="rolItem.roltoelichting"
					:disabled="loading"
					maxlength="1000"
					:label="t('zaakafhandelapp', 'Role explanation')"
					:error="!rolItem.roltoelichting" />

				<NcSelect
					v-bind="indicatieMachtigingOptions"
					v-model="indicatieMachtigingOptions.value"
					:disabled="loading"
					:inputLabel="t('zaakafhandelapp', 'Authorization indication')" />
			</div>

			<NcButton
				v-if="success === null"
				:disabled="
					loading
					|| !zaakOptions.value?.id
					|| !betrokkeneTypeOptions.value?.id
					|| !rolItem.roltype
					|| !rolItem.roltoelichting
				"
				variant="primary"
				@click="editRol()">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<ContentSaveOutline v-if="!loading" :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Save') }}
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcButton,
	NcLoadingIcon,
	NcModal,
	NcNoteCard,
	NcSelect,
	NcTextArea,
	NcTextField,
} from '@nextcloud/vue'
import ContentSaveOutline from 'vue-material-design-icons/ContentSaveOutline.vue'

export default {
	name: 'RolForm',
	components: {
		NcModal,
		NcTextField,
		NcButton,
		NcLoadingIcon,
		NcSelect,
		NcTextArea,
		NcNoteCard,
		// Icons
		ContentSaveOutline,
	},

	props: {
		zaakId: {
			type: String,
			required: false,
			default: null,
		},

		/**
		 * indicates if the modal should redirect the user to the detail page after saving
		 *
		 * @default true
		 */
		redirect: {
			type: Boolean,
			required: false,
			default: true,
		},
	},

	data() {
		return {
			rolItem: {
				zaak: '',
				betrokkene: '',
				betrokkeneType: '',
				afwijkendeNaamBetrokkene: '',
				roltype: '',
				omschrijving: '',
				omschrijvingGeneriek: '',
				roltoelichting: '',
				registratiedatum: '',
				indicatieMachtiging: '',
				contactpersoonRol: {
					emailadres: '',
					functie: '',
					telefoonnummer: '',
					naam: '',
				},

				statussen: [],
			},

			zaakOptionsLoading: false,
			zaakOptions: {
				options: [],
				value: null,
			},

			betrokkeneTypeOptions: {
				options: [
					{
						label: t('zaakafhandelapp', 'Natural person'),
						id: 'natuurlijk_persoon',
					},
					{
						label: t('zaakafhandelapp', 'Non-natural person'),
						id: 'niet_natuurlijk_persoon',
					},
					{
						label: t('zaakafhandelapp', 'Establishment'),
						id: 'vestiging',
					},
					{
						label: t('zaakafhandelapp', 'Organisational unit'),
						id: 'organisatorische_eenheid',
					},
					{ label: t('zaakafhandelapp', 'Employee'), id: 'medewerker' },
				],

				value: {
					label: t('zaakafhandelapp', 'Natural person'),
					id: 'natuurlijk_persoon',
				},
			},

			indicatieMachtigingOptions: {
				options: [
					{
						label: t('zaakafhandelapp', 'Authorized representative'),
						id: 'gemachtigde',
					},
					{
						label: t('zaakafhandelapp', 'Authorizer'),
						id: 'machtiginggever',
					},
				],

				value: null,
			},

			// =======
			success: null,
			loading: false,
			error: null,
			IS_EDIT: false,
		}
	},

	/**
	 * @spec openspec/specs/ui-modals/spec.md#REQ-004
	 */
	mounted() {
		this.IS_EDIT = !!rolStore.rolItem?.id

		if (this.IS_EDIT) {
			this.rolItem = {
				...this.rolItem,
				...rolStore.rolItem,
			}

			this.indicatieMachtigingOptions.value =
				this.indicatieMachtigingOptions.options.find(
					(option) => option.id === this.rolItem.indicatieMachtiging,
				)
			this.fetchData(rolStore.rolItem?.id)
		}

		this.fetchZaak(rolStore.rolItem?.zaak || this.zaakId)
	},

	methods: {
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModal() {
			navigationStore.setModal(null)
			rolStore.setZaakId(null)
			delete rolStore.extraData?.redirect
		},

		/**
		 * @param id
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		fetchData(id) {
			this.rolLoading = true

			rolStore
				.getRol(id)
				.then(({ data }) => {
					this.rolItem = {
						...this.rolItem,
						...data,
					}

					this.fetchZaak(data.zaak)
				})
				.finally(() => {
					this.rolLoading = false
				})
		},

		/**
		 * @param zaakId
		 * @spec openspec/specs/ui-modals/spec.md#REQ-005
		 */
		fetchZaak(zaakId) {
			this.zaakOptionsLoading = true

			zaakStore
				.refreshZakenList()
				.then(({ data }) => {
					const selectedZaak = data.find((zaak) => zaak.id === zaakId)

					this.zaakOptions.options = data.map((zaak) => ({
						label: zaak.identificatie,
						id: zaak.id,
					}))

					this.zaakOptions.value = selectedZaak
						? {
								label: selectedZaak.identificatie,
								id: selectedZaak.id,
							}
						: null
				})
				.finally(() => {
					this.zaakOptionsLoading = false
				})
		},

		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-003
		 */
		editRol() {
			this.loading = true

			rolStore
				.saveRol(
					{
						...this.rolItem,
						zaak: this.zaakOptions.value.id,
						betrokkeneType: this.betrokkeneTypeOptions.value.id,
						indicatieMachtiging:
							this.indicatieMachtigingOptions.value?.id || '',
					},
					{ redirect: this.redirect },
				)
				.then(({ response }) => {
					this.success = response.ok

					if (response.ok) setTimeout(this.closeModal, 2500)
				})
				.catch((e) => {
					this.success = false
					this.error =
						e.message
						|| t(
							'zaakafhandelapp',
							'Something went wrong while saving the role',
						)
				})
				.finally(() => {
					this.loading = false
				})
		},
	},
}
</script>

<style scoped>
.modalContent {
	margin: var(--zaa-margin-50, 12px);
	text-align: center;
}
</style>
