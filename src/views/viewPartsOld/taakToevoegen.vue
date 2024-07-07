<template>
	<div>
		<NcButton @click="showModal">Zaak Toevoegen</NcButton>
		<NcModal
			v-if="modal"
			ref="modalRef"
			@close="closeModal"
			name="Name inside modal">
			<div class="modal__content">
				<h2>Zaak Type</h2>
				<div class="form-group">
					<label for="pizza">What is the most important pizza item?</label>
					<NcSelect input-id="pizza" :options="['Cheese', 'Tomatos', 'Pineapples']" v-model="pizza" />
				</div>
				<h2>Please enter your name</h2>
				<div class="form-group">
					<NcTextField label="First Name" :value.sync="firstName" />
				</div>

				<NcButton
					:disabled="!firstName || !lastName || !pizza"
					@click="closeModal"
					type="primary">
					Submit
				</NcButton>
			</div>
		</NcModal>
	</div>
</template>
<script>

import { ref } from 'vue'

import { NcModal,NcButton,NcSelect,NcTextField  } from '@nextcloud/vue';

export default {
	name: "taakToevoegen",
	components: {
		NcModal,
		NcButton,
		NcSelect,
		NcTextField
	},
	setup() {
		return {
			modalRef: ref(null),
		}
	},
	data() {
		return {
			modal: false,
			firstName: '',
			lastName: '',
			pizza: [],
		}
	},
	methods: {
		showModal() {
			this.firstName = ''
			this.lastName = ''
			this.modal = true
		},
		closeModal() {
			this.modal = false
		}
	}
}
</script>
<style scoped>
.modal__content {
	margin: 50px;
}

.modal__content h2 {
	text-align: center;
}

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
