<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addZaak'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Zaak starten</h2>
			<div class="form-group">
				<NcTextField label="Naam" />
			</div>
			<div v-if="succesMessage" class="success">
				Zaak succesvol gestart
			</div>

			<NcButton :disabled="!name" type="primary" @click="addZaak">
				Starten
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField } from '@nextcloud/vue'

export default {
	name: 'AddZaak',
	components: {
		NcModal,
		NcTextField,
		NcButton,
	},
	data() {
		return {
			succesMessage: false,

		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		addZaak() {
			this.succesMessage = true
			this.name = ''
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
