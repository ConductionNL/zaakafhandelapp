<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcDialog :name="t('zaakafhandelapp', 'Extend case')"
		size="normal"
		label-id="extendZaakModal"
		:close-on-click-outside="false"
		@closing="closeModal">
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<NcNoteCard v-if="success" type="success">
			{{ success }}
		</NcNoteCard>

		<p class="explanation">
			{{ t('zaakafhandelapp', 'Extending the case shifts its planned and statutory deadlines forward by the chosen duration (Awb art. 4:14, verdaging — single use).') }}
		</p>

		<NcTextArea v-model="reden"
			:label="t('zaakafhandelapp', 'Reason for extension')"
			:disabled="loading"
			required />

		<NcTextField v-model="durationDays"
			type="number"
			:label="maxDays ? t('zaakafhandelapp', 'Extension in days (max {max})', { max: maxDays }) : t('zaakafhandelapp', 'Extension in days')"
			:disabled="loading"
			:max="maxDays || undefined"
			min="1" />

		<template #actions>
			<NcButton variant="secondary" @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Cancel') }}
			</NcButton>
			<NcButton variant="primary"
				:disabled="loading || !reden.trim() || !validDuration"
				@click="submit">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<CalendarPlus v-else :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Extend') }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcDialog, NcTextArea, NcTextField, NcNoteCard, NcLoadingIcon } from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'
import CalendarPlus from 'vue-material-design-icons/CalendarPlus.vue'

export default {
	name: 'ExtendZaak',
	components: {
		NcDialog,
		NcButton,
		NcTextArea,
		NcTextField,
		NcNoteCard,
		NcLoadingIcon,
		Cancel,
		CalendarPlus,
	},
	data() {
		return {
			reden: '',
			durationDays: '',
			loading: false,
			error: '',
			success: '',
		}
	},
	computed: {
		/**
		 * Maximum extension in days, parsed from the zaaktype's verlengingstermijn
		 * (ISO 8601 duration or plain day count); null when unset.
		 *
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		maxDays() {
			const termijn = zaakStore.zaakItem?.zaaktypeObject?.verlengingstermijn
				|| zaakStore.zaakItem?.verlengingstermijn
			return this.durationToDays(termijn)
		},
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		validDuration() {
			const n = parseInt(this.durationDays, 10)
			if (isNaN(n) || n <= 0) {
				return false
			}
			return !this.maxDays || n <= this.maxDays
		},
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		durationToDays(value) {
			if (!value) {
				return null
			}
			if (/^\d+$/.test(value)) {
				return parseInt(value, 10)
			}
			const m = String(value).match(/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)W)?(?:(\d+)D)?$/)
			if (!m) {
				return null
			}
			const [, y, mo, w, d] = m
			return (parseInt(y || 0, 10) * 365) + (parseInt(mo || 0, 10) * 30) + (parseInt(w || 0, 10) * 7) + parseInt(d || 0, 10)
		},
		/**
		 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-007
		 */
		async submit() {
			this.loading = true
			this.error = ''
			const zaak = { ...zaakStore.zaakItem }
			zaak.verlenging = { reden: this.reden.trim(), duur: `P${parseInt(this.durationDays, 10)}D` }
			try {
				await zaakStore.saveZaak(zaak)
				this.success = t('zaakafhandelapp', 'Case extended.')
				await zaakStore.getZaak(zaak.id, { setItem: true })
				setTimeout(() => this.closeModal(), 800)
			} catch (err) {
				console.error(err)
				this.error = t('zaakafhandelapp', 'The extension was refused. The case type may not allow it, the duration may exceed the allowed term, or the case is already extended or suspended.')
			} finally {
				this.loading = false
			}
		},
		/**
		 * @spec openspec/specs/ui-modals/spec.md#REQ-001
		 */
		closeModal() {
			navigationStore.setModal(null)
		},
	},
}
</script>

<style scoped>
.explanation {
	color: var(--color-text-maxcontrast);
	margin-block-end: 12px;
}
</style>
