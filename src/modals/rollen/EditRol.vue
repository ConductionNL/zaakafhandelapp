<script setup>
import { store } from '../../store.js'
</script>

<template>
	<NcModal v-if="store.modal === 'editRol'" ref="modalRef" @close="store.setModal(false)">
		<div class="modal__content">
			<h2>Rol aanpassen</h2>

			<div class="form-group">
				<NcTextField label="Omschrijving"
					:value.sync="rol.omschrijving"
					required="true"
					:loading="rolLoading" />
			</div>
			<div v-if="succesMessage" class="success">
				Rol succesvol opgeslagen
			</div>
			<NcButton :disabled="!rol.omschrijving" type="primary" @click="editRol">
				Opslaan
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue'

export default {
	name: 'EditRol',
	components: {
		NcModal,
		NcTextField,
		NcTextArea,
		NcButton,
	},
	data() {
		return {
			rol: {
				omschrijving: '',
			},
			succesMessage: false,
			hasUpdated: false,
		}
	},
	updated() {
		if (store.modal === 'editRol' && this.hasUpdated) {
			if (this.rol === store.rolItem) return
			this.hasUpdated = false
		}
		if (store.modal === 'editRol' && !this.hasUpdated) {
			this.fetchData(store.rolId)
			this.hasUpdated = true
			this.rol = store.rolItem
		}
	},
	methods: {
		fetchData(id) {
			this.rolLoading = true
			fetch(
				`/index.php/apps/zaakafhandelapp/api/rollen/${id}`,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.rol = data
						// this.oldZaakId = id
					})
					this.rolLoading = false
				})
				.catch((err) => {
					console.error(err)
					// this.oldZaakId = id
					this.rolLoading = false
				})
		},
		closeModal() {
			store.modal = false
		},
		editRol() {
			fetch(
				`/index.php/apps/zaakafhandelapp/api/rollen/${store.rolId}`,
				{
					method: 'PUT',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(this.rol),
				},
			).then((response) => {
				this.succesMessage = true
				setTimeout(() => (this.succesMessage = false), 2500)
			}).catch((err) => {
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
