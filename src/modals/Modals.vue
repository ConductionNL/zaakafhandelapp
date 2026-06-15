<script setup>
import { navigationStore, rolStore } from '../store/store.js'
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
		<ViewContactMoment v-if="navigationStore.viewModal === 'viewContactMoment'" />
		<DeleteContactMoment v-if="navigationStore.modal === 'deleteContactMoment'" />
		<!-- zaaktypen -->
		<ZaaktypeForm v-if="navigationStore.modal === 'zaaktypeForm'" />
		<DeleteZaaktype v-if="navigationStore.modal === 'deleteZaaktype'" />
		<!-- klanten -->
		<EditKlant />
		<ViewKlantAuditTrail v-if="navigationStore.modal === 'viewKlantAuditTrail'" />
		<ViewKlant v-if="navigationStore.modal === 'viewKlant'" />
		<DeleteKlant v-if="navigationStore.modal === 'deleteKlant'" />
		<!-- berichten -->
		<EditBericht />
		<ViewBerichtAuditTrail v-if="navigationStore.modal === 'viewBerichtAuditTrail'" />
		<!-- taken -->
		<EditTaak v-if="navigationStore.modal === 'editTaak'" />
		<ViewTaakAuditTrail v-if="navigationStore.modal === 'viewTaakAuditTrail'" />
		<!-- rollen -->
		<RolForm v-if="navigationStore.modal === 'rolForm'" :zaak-id="rolStore.zaakId" :redirect="rolStore.extraData?.redirect" />
		<DeleteRol v-if="navigationStore.modal === 'deleteRol'" />
		<!-- medewerkers -->
		<EditMedewerker v-if="navigationStore.modal === 'editMedewerker'" />
		<!-- resultaat -->
		<ResultaatForm v-if="navigationStore.modal === 'resultaatForm'" />
		<DeleteResultaat v-if="navigationStore.modal === 'deleteResultaat'" />
		<!-- besluiten -->
		<BesluitForm v-if="navigationStore.modal === 'besluitForm'" />
		<DeleteBesluit v-if="navigationStore.modal === 'deleteBesluit'" />
		<!-- documenten -->
		<DocumentForm v-if="navigationStore.modal === 'documentForm'" />
		<DeleteDocument v-if="navigationStore.modal === 'deleteDocument'" />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import WidgetZaakForm from './zaken/WidgetZaakForm.vue'
import ZaaktypeForm from './zaakTypen/ZaaktypeForm.vue'
import DeleteZaaktype from './zaakTypen/DeleteZaaktype.vue'
import EditKlant from './klanten/EditKlant.vue'
import ViewKlantAuditTrail from './klanten/ViewKlantAuditTrail.vue'
import EditBericht from './berichten/EditBericht.vue'
import ViewBerichtAuditTrail from './berichten/ViewBerichtAuditTrail.vue'
import EditTaak from './taken/EditTaak.vue'
import ViewTaakAuditTrail from './taken/ViewTaakAuditTrail.vue'
import ViewZaakAuditTrail from './zaken/ViewZaakAuditTrail.vue'
import ViewKlant from './klanten/ViewKlant.vue'
import DeleteKlant from './klanten/DeleteKlant.vue'
import DeleteContactMoment from './contactMomenten/DeleteContactMoment.vue'
import EditMedewerker from './medewerkers/EditMedewerker.vue'
import AddBerichtToZaak from './zaken/AddBerichtToZaak.vue'
import AddTaakToZaak from './zaken/AddTaakToZaak.vue'
import ContactMomentenForm from './contactMomenten/ContactMomentenForm.vue'
import AddRolToZaak from './zaken/AddRolToZaak.vue'
import ResultaatForm from './resultaten/ResultaatForm.vue'
import DeleteResultaat from './resultaten/DeleteResultaat.vue'
import BesluitForm from './besluiten/BesluitForm.vue'
import DeleteBesluit from './besluiten/DeleteBesluit.vue'
import ViewContactMoment from './contactMomenten/ViewContactMoment.vue'
import DocumentForm from './documenten/DocumentForm.vue'
import DeleteDocument from './documenten/DeleteDocument.vue'
import RolForm from './rollen/RolForm.vue'
import DeleteRol from './rollen/DeleteRol.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		WidgetZaakForm,
		ZaaktypeForm,
		DeleteZaaktype,
		EditKlant,
		ViewKlantAuditTrail,
		EditBericht,
		ViewBerichtAuditTrail,
		EditTaak,
		ViewTaakAuditTrail,
		ViewZaakAuditTrail,
		ViewKlant,
		DeleteKlant,
		DeleteContactMoment,
		EditMedewerker,
		AddBerichtToZaak,
		AddTaakToZaak,
		ContactMomentenForm,
		AddRolToZaak,
		ResultaatForm,
		DeleteResultaat,
		BesluitForm,
		DeleteBesluit,
		ViewContactMoment,
		DocumentForm,
		DeleteDocument,
		RolForm,
		DeleteRol,
	},
}
</script>
