<template>
	<NcContent appName="dsonextcloud">
		<Navigation selected="zaken" />
		<NcAppContent>
			<template #list>
				<ZakenList />
			</template>
			<template #default>
				<ZaakDetails />
			</template>
		</NcAppContent>
		<ZaakSidebar />
  </NcContent>
</template>
<script>

import ZakenList from './viewParts/ZakenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ZaakDetails from './viewParts/ZaakDetails.vue';
import ZaakSidebar from './viewParts/ZaakSidebar.vue';
import { NcAppContent,NcContent } from '@nextcloud/vue';


export default {
  name: "app",
  components: {
	ZakenList,
    Navigation,
	ZaakDetails,
	  ZaakSidebar,
    NcAppContent, NcContent

  }

}
</script>
