<template>
	<NcContent appName="dsonextcloud">
    <Navigation selected="zaak_typen" />

	  <NcAppContent>
		  <ZaakTypenList />
	  </NcAppContent>
	  <ZaakTypeDetails />
	</NcContent>
</template>
<script>

import ZaakTypenList from './viewParts/ZaakTypenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ZaakTypeDetails from './viewParts/ZaakTypeDetails.vue';
import { NcAppContent,NcContent } from '@nextcloud/vue';


export default {
	name: "app",
	components: {
		ZaakTypenList,
		Navigation,
		ZaakTypeDetails,
		NcAppContent,NcContent
	}

}
</script>
