<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddTaak />
		<EditTaak />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddTaak from './taken/AddTaak.vue'
import EditTaak from './taken/EditTaak.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddTaak,
		EditTaak,
	},
}
</script>
