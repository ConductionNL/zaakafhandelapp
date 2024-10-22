<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder -->
	<div>
		<ZaakForm v-if="navigationStore.modal === 'zaakForm'" />
		<AddKlant />
		<EditKlant />
		<AddBericht />
		<EditBericht />
		<AddTaak />
		<EditTaak />
		<AddRol />
		<EditRol />
	</div>
</template>

<script>
import ZaakForm from './zaken/ZaakForm.vue'
import AddKlant from './klanten/AddKlant.vue'
import EditKlant from './klanten/EditKlant.vue'
import AddBericht from './berichten/AddBericht.vue'
import EditBericht from './berichten/EditBericht.vue'
import AddTaak from './taken/AddTaak.vue'
import EditTaak from './taken/EditTaak.vue'
import AddRol from './rollen/AddRol.vue'
import EditRol from './rollen/EditRol.vue'

export default {
	name: 'Modals',
	components: {
		ZaakForm,
		AddKlant,
		EditKlant,
		AddBericht,
		EditBericht,
		AddTaak,
		EditTaak,
		AddRol,
		EditRol,
	},
}
</script>
