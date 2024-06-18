<template>
	<div class="container">
		<Navigation selected="taken" />

		<NcAppContent>
			<TakenList />
		</NcAppContent>
		<TaakDetails />
	</div>
</template>
<script>

import TakenList from './viewParts/TakenList.vue';
import Navigation from './viewParts/Navigation.vue';
import TaakDetails from './viewParts/TaakDetails.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		TakenList,
		Navigation,
		TaakDetails,
		NcAppContent
	}

}
</script>
<style>
.container {
	display: flex;
	width: 100%;
}
</style>
