<template>
  <div class="container">
    <Navigation selected="zaak_typen" />

	  <NcAppContent>
		  <ZaakTypenList />
	  </NcAppContent>
	  <ZaakTypeDetails />
  </div>
</template>
<script>

import ZaakTypenList from './viewParts/ZaakTypenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ZaakTypeDetails from './viewParts/ZaakTypeDetails.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		ZaakTypenList,
		Navigation,
		ZaakTypeDetails,
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
