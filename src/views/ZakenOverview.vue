<template>
  <div class="container">
    <div>
      <h1 class="heading">Zaken</h1>
      <ZakenOverviewTable />
     
    </div>
  </div>
</template>
<script>
import ZakenOverviewTable from './viewParts/ZakenOverviewTable.vue';


export default {
  name: "app",
  components: {
    ZakenOverviewTable
  }

}
</script>
<style>
.container {
  background-color: #ffffff;
  margin-block-start: 75px;
  margin-inline-end: auto;
  margin-inline-start: auto;
  max-inline-size: calc(1140px - 24px - 24px);
  padding-inline-end: 24px;
  padding-inline-start: 24px;
}

.heading {
  color: #275937;
}
</style>
