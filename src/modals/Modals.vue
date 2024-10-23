<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder -->
	<div>
		<ZaakForm v-if="navigationStore.modal === 'zaakForm'" />
		<EditZaakType />
		<EditKlant />
		<ViewKlantAuditTrail v-if="navigationStore.modal === 'viewKlantAuditTrail'" />
		<EditBericht />
		<ViewBerichtAuditTrail v-if="navigationStore.modal === 'viewBerichtAuditTrail'" />
		<EditTaak />
		<ViewTaakAuditTrail v-if="navigationStore.modal === 'viewTaakAuditTrail'" />
		<EditRol />
		<ViewZaakAuditTrail v-if="navigationStore.modal === 'viewZaakAuditTrail'" />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import EditZaakType from './zaakTypen/EditZaakType.vue'
import EditKlant from './klanten/EditKlant.vue'
import ViewKlantAuditTrail from './klanten/ViewKlantAuditTrail.vue'
import EditBericht from './berichten/EditBericht.vue'
import ViewBerichtAuditTrail from './berichten/ViewBerichtAuditTrail.vue'
import EditTaak from './taken/EditTaak.vue'
import ViewTaakAuditTrail from './taken/ViewTaakAuditTrail.vue'
import EditRol from './rollen/EditRol.vue'
import ViewZaakAuditTrail from './zaken/ViewZaakAuditTrail.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		EditZaakType,
		EditKlant,
		ViewKlantAuditTrail,
		EditBericht,
		ViewBerichtAuditTrail,
		EditTaak,
		ViewTaakAuditTrail,
		EditRol,
		ViewZaakAuditTrail,
	},
}
</script>
