<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'addRol'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Rol aanmaken</h2>
			<div class="form-group">
				<NcTextField label="Omschrijving" required="true" :value.sync="omschrijving" />
			</div>
			<div v-if="succesMessage" class="success">
				Rol succesvol aangemaakt
			</div>

			<NcButton :disabled="!omschrijving" type="primary" @click="addRol">
				Aanmaken
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'AddRol',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
	},
	data() {
		return {
			omschrijving: '',
		}
	},
	methods: {
		closeModal() {
			store.modal = false
		},
		addRol() {
			this.$emit('rol', this.onderwerp)
			fetch(
				'/index.php/apps/zaakafhandelapp/rollen/api',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({
						omschrijving: this.omschrijving,
					}),
				},
			)
				.then((response) => {
					this.succesMessage = true
					setTimeout(() => (this.succesMessage = false), 2500)
				})
				.catch((err) => {
					console.error(err)
				})
		},
	},
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

.rolDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}

.success {
    color: green;
}
</style>
