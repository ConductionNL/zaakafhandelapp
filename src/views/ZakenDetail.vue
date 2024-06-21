<template>
  <div class="container">
    <div v-if="!loading" id="app-content">
      <!-- app-content-wrapper is optional, only use if app-content-list  -->
      <div class="zakenContainer">
        <h1 class="h1">{{ zaak.omschrijving }}</h1>
        <div class="grid">
          <div class="gridContent">
            <h4>SamenwerkingsID:</h4>
            <span>{{ zaak.identificatie }}</span>
          </div>
          <div class="gridContent">
            <h4>Aangemaakt Door:</h4>
            <span>{{ zaak.bronorganisatie }}</span>
          </div>
          <div class="gridContent">
            <h4>Startdatum:</h4>
            <span>{{ zaak.startdatum }}</span>
          </div>
          <div class="gridContent">
            <h4>Status:</h4>
            <span>{{ zaak.archiefstatus }}</span>
          </div>
        </div>
        <div>
          <h4>Beschrijving samenwerking:</h4>
          <p>{{ zaak.toelichting }}</p>
        </div>
      </div>
    </div>
    <div v-if="loading">
      <VueSkeletonLoader type="rectangle" :width="400" :height="200" animation="fade" />
    </div>
  </div>
</template>
<script>

import VueSkeletonLoader from 'skeleton-loader-vue';
import Navigation from './viewParts/Navigation.vue';
import { TEMP_AUTHORIZATION_KEY } from '../data/TempAuthKey';

export default {
  name: "ZakenDetail",
  props: {
    zaakId: {
      type: String,
      required: true
    },
  },
  watch: {
    zaakId: {
      handler(newZaakId) {
        this.fetchData(newZaakId)
      },
      deep: true
    }
  },
  components: {
    Navigation,
    VueSkeletonLoader
  },
  data() {
    return {
      zaak: '',
      oldZaakId: '',
      loading: false,
    }
  },
  mounted() {
    this.fetchData(this.zaakId)
  },
  methods: {
    fetchData(id) {
      this.loading = true,
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
              this.oldZaakId = id
            })
            this.loading = false
          })
          .catch((err) => {
            console.error(err)
            this.oldZaakId = id
            this.loading = false
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
