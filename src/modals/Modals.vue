<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddKlant />
		<EditKlant />
		<AddTaak />
		<EditTaak />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddKlant from './klanten/AddKlant.vue'
import EditKlant from './klanten/EditKlant.vue'
import AddTaak from './taken/AddTaak.vue'
import EditTaak from './taken/EditTaak.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddKlant,
		EditKlant,
		AddTaak,
		EditTaak,
	},
}
</script>
