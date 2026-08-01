<script setup>
import { translate as t } from '@nextcloud/l10n'
import { navigationStore, zaakStore } from '../../store/store.js'
</script>

<template>
	<NcDialog :name="isSuspended ? t('zaakafhandelapp', 'Resume case') : t('zaakafhandelapp', 'Suspend case')"
		size="normal"
		label-id="suspendZaakModal"
		:close-on-click-outside="false"
		@closing="closeModal">
		<NcNoteCard v-if="error" type="error">
			{{ error }}
		</NcNoteCard>

		<NcNoteCard v-if="success" type="success">
			{{ success }}
		</NcNoteCard>

		<p v-if="isSuspended" class="explanation">
			{{ t('zaakafhandelapp', 'Resuming the case shifts its planned and statutory deadlines forward by the elapsed suspension period (Awb art. 4:15).') }}
		</p>
		<p v-else class="explanation">
			{{ t('zaakafhandelapp', 'Suspending the case pauses its beslistermijn. A reason is required.') }}
		</p>

		<NcTextArea v-if="!isSuspended"
			v-model="reden"
			:label="t('zaakafhandelapp', 'Reason for suspension')"
			:disabled="loading"
			required />

		<template #actions>
			<NcButton variant="secondary" @click="closeModal">
				<template #icon>
					<Cancel :size="20" />
				</template>
				{{ t('zaakafhandelapp', 'Cancel') }}
			</NcButton>
			<NcButton variant="primary"
				:disabled="loading || (!isSuspended && !reden.trim())"
				@click="submit">
				<template #icon>
					<NcLoadingIcon v-if="loading" :size="20" />
					<PlayOutline v-else-if="isSuspended" :size="20" />
					<PauseOutline v-else :size="20" />
				</template>
				{{ isSuspended ? t('zaakafhandelapp', 'Resume') : t('zaakafhandelapp', 'Suspend') }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script>
import { NcButton, NcDialog, NcTextArea, NcNoteCard, NcLoadingIcon } from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'
import PauseOutline from 'vue-material-design-icons/PauseCircleOutline.vue'
import PlayOutline from 'vue-material-design-icons/PlayCircleOutline.vue'

export default {
	name: 'SuspendZaak',
	components: {
		NcDialog,
		NcButton,
		NcTextArea,
		NcNoteCard,
		NcLoadingIcon,
		Cancel,
		PauseOutline,
		PlayOutline,
	},
	data() {
		return {
			reden: '',
			loading: false,
			error: '',
			success: '',
		}
	},
	computed: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-007
		 */
		isSuspended() {
			return zaakStore.zaakItem?.opschorting?.indicatie === true
		},
	},
	methods: {
		/**
		 * @spec openspec/specs/zgw-case-lifecycle/spec.md#REQ-006
		 */
		async submit() {
			this.loading = true
			this.error = ''
			const zaak = { ...zaakStore.zaakItem }
			if (this.isSuspended) {
				zaak.opschorting = { ...zaak.opschorting, indicatie: false }
			} else {
				zaak.opschorting = { indicatie: true, reden: this.reden.trim() }
			}
			try {
				await zaakStore.saveZaak(zaak)
				this.success = this.isSuspended
					? t('zaakafhandelapp', 'Case resumed.')
					: t('zaakafhandelapp', 'Case suspended.')
				await zaakStore.getZaak(zaak.id, { setItem: true })
				setTimeout(() => this.closeModal(), 800)
			} catch (err) {
				console.error(err)
				this.error = t('zaakafhandelapp', 'The operation was refused. The case type may not allow this, or the case is closed.')
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
