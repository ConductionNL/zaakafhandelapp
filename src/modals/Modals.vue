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
		<AddRolToZaak v-if="navigationStore.modal === 'addRolToZaak'" />
		<!-- contactmomenten -->
		<ContactMomentenForm v-if="navigationStore.modal === 'contactMomentenForm'" />
		<DeleteContactMoment v-if="navigationStore.modal === 'deleteContactMoment'" />
		<!-- zaaktypen -->
		<ZaaktypeForm v-if="navigationStore.modal === 'zaaktypeForm'" />
		<!-- klanten -->
		<EditKlant />
		<ViewKlantAuditTrail v-if="navigationStore.modal === 'viewKlantAuditTrail'" />
		<!-- berichten -->
		<EditBericht />
		<ViewBerichtAuditTrail v-if="navigationStore.modal === 'viewBerichtAuditTrail'" />
		<!-- taken -->
		<EditTaak v-if="navigationStore.modal === 'editTaak'" />
		<ViewTaakAuditTrail v-if="navigationStore.modal === 'viewTaakAuditTrail'" />
		<!-- rollen -->
		<EditRol />
		<!-- klant register -->
		<ViewKlantRegister v-if="navigationStore.modal === 'viewKlantRegister'" />
		<EditMedewerker v-if="navigationStore.modal === 'editMedewerker'" />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import WidgetZaakForm from './zaken/WidgetZaakForm.vue'
import ZaaktypeForm from './zaakTypen/ZaaktypeForm.vue'
import EditKlant from './klanten/EditKlant.vue'
import ViewKlantAuditTrail from './klanten/ViewKlantAuditTrail.vue'
import EditBericht from './berichten/EditBericht.vue'
import ViewBerichtAuditTrail from './berichten/ViewBerichtAuditTrail.vue'
import EditTaak from './taken/EditTaak.vue'
import ViewTaakAuditTrail from './taken/ViewTaakAuditTrail.vue'
import EditRol from './rollen/EditRol.vue'
import ViewZaakAuditTrail from './zaken/ViewZaakAuditTrail.vue'
import ViewKlantRegister from './klantRegister/ViewKlantRegister.vue'
import DeleteContactMoment from './contactMomenten/DeleteContactMoment.vue'
import EditMedewerker from './medewerkers/EditMedewerker.vue'
import AddBerichtToZaak from './zaken/AddBerichtToZaak.vue'
import AddTaakToZaak from './zaken/AddTaakToZaak.vue'
import ContactMomentenForm from './contactMomenten/ContactMomentenForm.vue'
import AddRolToZaak from './zaken/AddRolToZaak.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		WidgetZaakForm,
		ZaaktypeForm,
		EditKlant,
		ViewKlantAuditTrail,
		EditBericht,
		ViewBerichtAuditTrail,
		EditTaak,
		ViewTaakAuditTrail,
		EditRol,
		ViewZaakAuditTrail,
		ViewKlantRegister,
		DeleteContactMoment,
		EditMedewerker,
		AddBerichtToZaak,
		AddTaakToZaak,
		ContactMomentenForm,
		AddRolToZaak,
	},
}
</script>
