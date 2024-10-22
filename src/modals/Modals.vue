<script setup>
import { navigationStore } from '../store/store.js'
</script>

<template>
	<!-- Placeholder -->
	<div>
		<EditZaakType />
		<EditKlant />
		<EditBericht />
		<EditTaak />
		<EditRol />
		<EditZaak />
	</div>
</template>

<script>
import EditZaakType from './zaakTypen/EditZaakType.vue'
import EditKlant from './klanten/EditKlant.vue'
import EditBericht from './berichten/EditBericht.vue'
import EditTaak from './taken/EditTaak.vue'
import EditRol from './rollen/EditRol.vue'
import EditZaak from './zaken/EditZaak.vue'

export default {
	name: 'Modals',
	components: {
		EditZaakType,
		EditKlant,
		EditBericht,
		EditTaak,
		EditRol,
		EditZaak
	},
}
</script>
