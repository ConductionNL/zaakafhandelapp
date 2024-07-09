<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddKlant />
		<EditKlant />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddKlant from './klanten/AddKlant.vue'
import EditKlant from './klanten/EditKlant.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddKlant,
		EditKlant,
	},
}
</script>
