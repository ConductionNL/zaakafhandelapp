<template>
  <div class="container">
    <Navigation selected="zaken" />

    <NcAppContent>
      <ZakenList />
    </NcAppContent>
	  <ZaakDetails />
  </div>
</template>
<script>

import ZakenList from './viewParts/ZakenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ZaakDetails from './viewParts/ZaakDetails.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
  name: "app",
  components: {
	ZakenList,
    Navigation,
	ZaakDetails,
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
