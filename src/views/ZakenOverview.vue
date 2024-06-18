<template>
  <div class="container">
    <Navigation selected="zaken" />

    <NcAppContent>
      <ZakenOverviewList />
    </NcAppContent>
  </div>
</template>
<script>

import ZakenOverviewList from './viewParts/ZakenOverviewList.vue';
import Navigation from './viewParts/Navigation.vue';
import { NcAppContent } from '@nextcloud/vue';


export default {
  name: "app",
  components: {
    ZakenOverviewList,
    Navigation,
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
