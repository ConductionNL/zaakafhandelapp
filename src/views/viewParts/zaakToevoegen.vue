<template>
	<NcModal v-if="zaakAanmakenModal"  @close="closeModal">
		<div class="modal__content">
			<h2>Zaak aanmaken</h2>
			<div class="form-group">
				<NcTextField label="Identificatie" :value.sync="identificatie" />
			</div>
			<div class="form-group">
				<NcTextField label="Omschrijving" :value.sync="omschrijving" />
			</div>
			<div class="form-group">
				<NcTextArea label="Toelichting" :value.sync="toelichting" />
			</div>
			<div class="form-group">
				<NcTextField label="Bronorganisatie" :value.sync="bronorganisatie" />
			</div>
			<div class="form-group">
				<NcTextField label="VerantwoordelijkeOrganisatie" :value.sync="verantwoordelijkeOrganisatie" />
			</div>
			<div class="form-group">
				<NcTextField label="Zaaktype" :value.sync="zaaktype" />
			</div>
			<div class="form-group">
				<NcTextField label="Archiefstatus" :value.sync="archiefstatus" />
			</div>
			<div class="form-group">
				<NcTextField label="Startdatum" :value.sync="startdatum" />
			</div>


			<NcButton
				:disabled="!identificatie || !omschrijving || !toelichting || !bronorganisatie || !verantwoordelijkeOrganisatie || !zaaktype || !archiefstatus || !startdatum"
				@click="addZaak" type="primary">
				Submit
			</NcButton>
		</div>
	</NcModal>
</template>
<script>

import { inject } from 'vue'

import { NcModal,NcButton,NcSelect,NcTextField  } from '@nextcloud/vue';

export default {
	name: "zaakToevoegen",
	components: {
		NcModal,
		NcButton,
		NcSelect,
		NcTextField
	},
	setup() {
		return {
			zaakAanmakenModal: inject("zaakAanmakenModal")
		}
	},
	data() {
		return {
			firstName: '',
			lastName: '',
			pizza: [],
		}
	},
	methods: {
		showModal() {
			this.firstName = ''
			this.lastName = ''
			this.zaakAanmakenModal = true
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
