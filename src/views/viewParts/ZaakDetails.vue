<template>
	<NcEmptyContent name="Geen zaak" description="Nog geen zaak geselecteerd">
		<template #icon>
			<BriefcaseAccountOutline />
		</template>
		<template #action>
			<NcButton type="primary" @click="showModal">
				Zaak aanmaken
			</NcButton>

			<NcModal v-if="modal" ref="modalRef" @close="closeModal">
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

			<zaakToevoegen />
		</template>
	</NcEmptyContent>
</template>
<script>

import Vue from 'vue';
import zaakToevoegen from './zaakToevoegen.vue'
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline';
import { NcEmptyContent, NcButton, NcModal, NcTextField, NcSelect, NcEmojiPicker, NcTextArea } from '@nextcloud/vue';
import { TEMP_AUTHORIZATION_KEY } from '../../data/TempAuthKey';

export default {
	name: "ZaakDetails",
	setup() {
		return {
			modalRef: Vue.ref(null),
		}
	},
	data() {
		return {
			modal: false,
			identificatie: '',
			omschrijving: '',
			toelichting: '',
			bronorganisatie: '',
			verantwoordelijkeOrganisatie: '',
			zaaktype: '',
			archiefstatus: '',
			startdatum: '',
			loading: false,
		}
	},
	components: {
		NcEmptyContent,
		BriefcaseAccountOutline,
		NcButton,
		zaakToevoegen,
		NcModal,
		NcTextField,
		NcSelect,
		NcEmojiPicker,
		NcTextArea
	},
	methods: {
		showModal() {
			this.identificatie = ''
			this.omschrijving = ''
			this.toelichting = ''
			this.bronorganisatie = ''
			this.verantwoordelijkeOrganisatie = ''
			this.zaaktype = 'http://localhost/api/ztc/v1/zaaktypen/a1748dd6-50a3-464d-b95e-554e87298ce9'
			this.archiefstatus = ''
			this.startdatum = ''
			this.modal = true
		},
		closeModal() {
			this.modal = false
		},

		addZaak() {
			fetch(
				`https://api.test.common-gateway.commonground.nu/api/zrc/v1/zaken`,
				{
					method: 'POST',
					headers: {
						"Authorization": TEMP_AUTHORIZATION_KEY
					},
					body: JSON.stringify({
						identificatie: this.identificatie,
						omschrijving: this.omschrijving,
						toelichting: this.toelichting,
						bronorganisatie: this.bronorganisatie,
						verantwoordelijkeOrganisatie: this.verantwoordelijkeOrganisatie,
						zaaktype: this.zaaktype,
						archiefstatus: this.archiefstatus,
						startdatum: this.startdatum
					})
				},
			)
				.then((response) => {
					console.log(response)
				})
				.catch((err) => {
					console.error(err)
				})
		},
	}
}
</script>
<style>
.modal__content {
	margin: 50px;
	text-align: center;
}
</style>
