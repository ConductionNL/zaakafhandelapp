<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder -->
	<div>
		<ZaakForm v-if="navigationStore.modal === 'zaakForm'" />
		<EditZaakType />
		<EditKlant />
		<EditBericht />
		<EditTaak />
		<EditRol />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import EditZaakType from './zaakTypen/EditZaakType.vue'
import EditKlant from './klanten/EditKlant.vue'
import EditBericht from './berichten/EditBericht.vue'
import EditTaak from './taken/EditTaak.vue'
import EditRol from './rollen/EditRol.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		EditZaakType,
		EditKlant,
		EditBericht,
		EditTaak,
		EditRol,
	},
}
</script>
