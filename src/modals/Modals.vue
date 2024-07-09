<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddBericht />
		<EditBericht />
		<AddTaak />
		<EditTaak />
		<AddRol />
		<EditRol />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddBericht from './berichten/AddBericht.vue'
import EditBericht from './berichten/EditBericht.vue'
import AddTaak from './taken/AddTaak.vue'
import EditTaak from './taken/EditTaak.vue'
import AddRol from './rollen/AddRol.vue'
import EditRol from './rollen/EditRol.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddBericht,
		EditBericht,
		AddTaak,
		EditTaak,
		AddRol,
		EditRol,
	},
}
</script>
