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
import AddKlant from './zaken/AddKlant.vue'
import EditKlant from './zaken/EditKlant.vue'

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
