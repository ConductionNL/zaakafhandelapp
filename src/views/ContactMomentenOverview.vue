<template>
  <div class="container">
    <Navigation selected="contact_momenten" />

	  <NcAppContent>
		  <ContactMomentenList />
	  </NcAppContent>
	  <ContactMomentDetails />
  </div>
</template>
<script>

import ContactMomentenList from './viewParts/ContactMomentenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ContactMomentDetails from './viewParts/ContactMomentDetails.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		ContactMomentenList,
		Navigation,
		ContactMomentDetails,
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
