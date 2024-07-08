<template>
	<!-- Placeholder -->
	<div>
		<AddZaak />
		<EditZaak />
		<AddBericht />
		<EditBericht />
	</div>
</template>

<script>
import AddZaak from './zaken/AddZaak.vue'
import EditZaak from './zaken/EditZaak.vue'
import AddBericht from './zaken/berichten/AddBericht.vue'
import EditBericht from './zaken/berichten/EditBericht.vue'

export default {
	name: 'Modals',
	components: {
		AddZaak,
		EditZaak,
		AddBericht,
		EditBericht,
	},
}
</script>
