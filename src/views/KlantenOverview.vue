<template>
  <div class="container">
	<Navigation selected="klanten" />

	  <NcAppContent>
		  <KlantenList />
	  </NcAppContent>
	  <KlantDetails />
  </div>
</template>
<script>

import KlantenList from './viewParts/KlantenList.vue';
import Navigation from './viewParts/Navigation.vue';
import KlantDetails from './viewParts/KlantDetails.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		KlantenList,
		Navigation,
		KlantDetails,
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
