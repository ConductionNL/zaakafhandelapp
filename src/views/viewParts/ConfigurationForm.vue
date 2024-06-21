<template>
  Hier moet dan dus een formulier
</template>

<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

Vue.use(Loading);

export default {
  name: "ConfigurationForm",
  props: {
    msg: String,
  }
}
</script>
