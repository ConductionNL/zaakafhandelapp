<template>
  <div class="container">
    <Navigation />

    <div id="app-content">
      <!-- app-content-wrapper is optional, only use if app-content-list  -->
      <ZaakDetails />
    </div>
  </div>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Navigation from './viewParts/Navigation.vue';
import ZaakDetails from './viewParts/ZaakDetails.vue';
import { TEMP_AUTHORIZATION_KEY } from '../data/TempAuthKey';

Vue.use(Loading);

export default {
  name: "ZakenDetail",
  components: {
    Navigation
  },
  data() {
    return {
      zaak: '',
      fullPage: false,
      currentPage: 1,
      perPageLimit: 10,
    }
  },
  mounted() {
    const url = new URL(window.location)
    const id = url.pathname.split("/").pop()
    this.fetchData(id)
  },
  methods: {
    fetchData(id) {
      let loader = this.$loading.show({
        container: this.fullPage ? null : this.$refs.table,
        canCancel: true,
        onCancel: this.onCancel,
        color: "#39870c",
        loader: "dots",
      });
      fetch(
        `https://api.test.common-gateway.commonground.nu/api/zrc/v1/zaken/${id}`,
        {
          method: 'GET',
          headers: {
            "Authorization": TEMP_AUTHORIZATION_KEY
          }
        },
      )
        .then((response) => {
          response.json().then((data) => {
            this.zaak = data
          })
          loader.hide()
        })
        .catch((err) => {
          console.error(err)
          loader.hide()
        })
    },
  },
}

</script>
<style>
.container {
  display: flex;
  width: 100%;
}

h4 {
  font-weight: bold
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

.grid {
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 1fr 1fr;
  margin-block-start: 50px;
  margin-block-end: 50px;
}

.gridContent {
  display: flex;
  justify-content: space-between;
}
</style>
