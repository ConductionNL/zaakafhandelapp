<template>
  <div class="container">
	  <Navigation />



    <div id="app-content">
      <!-- app-content-wrapper is optional, only use if app-content-list  -->
      <div class="zakenContainer">
        <h1 class="h1">Zaak Typen</h1>
        <ZakenOverviewTable />
      </div>
    </div>
  </div>
</template>
<script>
import ZakenOverviewTable from './viewParts/ZakenOverviewTable.vue';
import Navigation from './viewParts/Navigation.vue';


export default {
  name: "app",
  components: {
    ZakenOverviewTable,
	  Navigation
  }

}
</script>
<style>
.container {
  display: flex;
  width: 100%;
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.zakenContainer {
  margin-block-start: 20px;
  margin-inline-start: 20px;
  margin-inline-end: 20px;
}
</style>
