<template>
  <div class="container">
    <Navigation selected="configuration" />

    <div id="app-content">
      <h1 class="h1">Configuratie</h1>
      <ConfigurationForm />
    </div>
  </div>
</template>
<script>

import ConfigurationForm from './viewParts/ConfigurationForm.vue';
import Navigation from './viewParts/Navigation.vue';


export default {
  name: "app",
  components: {
    ConfigurationForm,
    Navigation
  }
}
</script>
