<template>
	<NcContent appName="dsonextcloud">
	<Navigation selected="klanten" />

	  <NcAppContent>
		  <KlantenList />
	  </NcAppContent>
	  <KlantDetails />
	</NcContent>
</template>
<script>

import KlantenList from './viewParts/KlantenList.vue';
import Navigation from './viewParts/Navigation.vue';
import KlantDetails from './viewParts/KlantDetails.vue';
import { NcAppContent,NcContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		KlantenList,
		Navigation,
		KlantDetails,
		NcAppContent,
		NcContent
	}

}
</script>
