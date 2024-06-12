<template>
  <div class="container">
    <div>
      <hr>
      <h1 class="heading">Samenwerken aan behandelen</h1>
      <h2 class="heading">Overzicht samenwerkingen</h2>
      <hr>
      <button class="button">Samenwerking starten</button>
    </div>
  </div>
</template>
<script>
export default {
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
  background-color: transparent !important;
  font-weight: normal !important;
  border-width: 1px !important;
  border-style: solid !important;
  border-color: #39870c !important;
  border-radius: 3px !important;
}

.button:hover {
  color: #285e08 !important;
  background-color: transparent !important;
  border-color: #285e08 !important;
}
</style>
