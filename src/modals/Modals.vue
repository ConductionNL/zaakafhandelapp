<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddBericht />
		<EditBericht />
		<AddTaak />
		<EditTaak />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddBericht from './zaken/berichten/AddBericht.vue'
import EditBericht from './zaken/berichten/EditBericht.vue'
import AddTaak from './taken/AddTaak.vue'
import EditTaak from './taken/EditTaak.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddBericht,
		EditBericht,
		AddTaak,
		EditTaak,
	},
}
</script>
