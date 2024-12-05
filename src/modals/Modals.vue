<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder -->
	<div>
		<!-- zaak -->
		<ZaakForm v-if="navigationStore.modal === 'zaakForm'" />
		<ViewZaakAuditTrail v-if="navigationStore.modal === 'viewZaakAuditTrail'" />
		<WidgetZaakForm v-if="navigationStore.modal === 'widgetZaakForm'" />
		<AddBerichtToZaak v-if="navigationStore.modal === 'addBerichtToZaak'" />
		<AddTaakToZaak v-if="navigationStore.modal === 'addTaakToZaak'" />
		<!-- ==== -->
		<EditZaakType />
		<EditKlant />
		<ViewKlantAuditTrail v-if="navigationStore.modal === 'viewKlantAuditTrail'" />
		<EditBericht />
		<ViewBerichtAuditTrail v-if="navigationStore.modal === 'viewBerichtAuditTrail'" />
		<EditTaak v-if="navigationStore.modal === 'editTaak'" />
		<WidgetTaakForm v-if="navigationStore.modal === 'widgetTaakForm'" />
		<ViewTaakAuditTrail v-if="navigationStore.modal === 'viewTaakAuditTrail'" />
		<EditRol />
		<ViewKlantRegister v-if="navigationStore.modal === 'viewKlantRegister'" />
		<DeleteContactMoment v-if="navigationStore.modal === 'deleteContactMoment'" />
		<EditMedewerker v-if="navigationStore.modal === 'editMedewerker'" />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import WidgetZaakForm from './zaken/WidgetZaakForm.vue'
import EditZaakType from './zaakTypen/EditZaakType.vue'
import EditKlant from './klanten/EditKlant.vue'
import ViewKlantAuditTrail from './klanten/ViewKlantAuditTrail.vue'
import EditBericht from './berichten/EditBericht.vue'
import ViewBerichtAuditTrail from './berichten/ViewBerichtAuditTrail.vue'
import EditTaak from './taken/EditTaak.vue'
import ViewTaakAuditTrail from './taken/ViewTaakAuditTrail.vue'
import WidgetTaakForm from './taken/WidgetTaakForm.vue'
import EditRol from './rollen/EditRol.vue'
import ViewZaakAuditTrail from './zaken/ViewZaakAuditTrail.vue'
import ViewKlantRegister from './klantRegister/ViewKlantRegister.vue'
import DeleteContactMoment from './contactMomenten/DeleteContactMoment.vue'
import EditMedewerker from './medewerkers/EditMedewerker.vue'
import AddBerichtToZaak from './zaken/AddBerichtToZaak.vue'
import AddTaakToZaak from './zaken/AddTaakToZaak.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		WidgetZaakForm,
		EditZaakType,
		EditKlant,
		ViewKlantAuditTrail,
		EditBericht,
		ViewBerichtAuditTrail,
		EditTaak,
		WidgetTaakForm,
		ViewTaakAuditTrail,
		EditRol,
		ViewZaakAuditTrail,
		ViewKlantRegister,
		DeleteContactMoment,
		EditMedewerker,
		AddBerichtToZaak,
		AddTaakToZaak,
	},
}
</script>
