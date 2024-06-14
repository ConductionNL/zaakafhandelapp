<template>
  <div class="container">
    <div>
      <hr>
      <h1 class="heading">Samenwerken aan behandelen</h1>
      <h2 class="heading">Overzicht samenwerkingen</h2>
      <hr>
      <button class="button">Samenwerking starten</button>
      <samenwerking-overview-table />
    </div>
  </div>
</template>
<script>
import SamenwerkingOverviewTable from './SamenwerkingOverviewTable'


export default {
  name: "app",
  components: {
    SamenwerkingOverviewTable
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

.button {
  padding: 12px 12px !important;
  color: #39870c !important;
  background-color: #ffffff !important;
  font-weight: normal !important;
  border-width: 1px !important;
  border-style: solid !important;
  border-color: #39870c !important;
  border-radius: 4px !important;
  font-size: 1rem;
}

.button:hover {
  color: #ffffff !important;
  background-color: #39870c !important;
  border-color: #39870c !important;
}
</style>
