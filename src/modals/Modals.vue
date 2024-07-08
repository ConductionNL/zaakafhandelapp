<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
	},
}
</script>
