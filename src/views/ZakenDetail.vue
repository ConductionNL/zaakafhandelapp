<template>
  <div>
    <div v-if="!loading" id="app-content" class="zakenDetailContainer">
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

      <div class="tabContainer">
        <BTabs content-class="mt-3" justified>
          <BTab title="Eigenschappen" active>
            <p class="tabPanel">Eigenschappen</p>
          </BTab>
          <BTab title="Bestanden" active>
            <p class="tabPanel">Bestanden</p>
          </BTab>
          <BTab title="Taken" active>
            <p class="tabPanel">Taken</p>
          </BTab>
          <BTab title="Rollen" active>
            <p class="tabPanel">Rollen</p>
          </BTab>
          <BTab title="Contact Momenten" active>
            <p class="tabPanel">Contact Momenten</p>
          </BTab>
          <BTab title="Publicaties" active>
            <p class="tabPanel">Publicaties</p>
          </BTab>
        </BTabs>
      </div>
    </div>
    <div v-if="loading" class="zakenDetailContainer">
      <VueSkeletonLoader type="rectangle" :width="400" :height="200" animation="fade" />
    </div>
  </div>
</template>

<script>
import VueSkeletonLoader from 'skeleton-loader-vue';
import Navigation from './viewParts/Navigation.vue';
import { TEMP_AUTHORIZATION_KEY } from '../data/TempAuthKey';
import { BTabs, BTab } from 'bootstrap-vue'

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
    VueSkeletonLoader,
    BTabs,
    BTab,
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
.zakenDetailContainer {
  margin-block-start: var(--zaa-margin-20);
  margin-inline-start: var(--zaa-margin-20);
  margin-inline-end: var(--zaa-margin-20);
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
  margin-block-start: var(--zaa-margin-20);
  margin-block-end: var(--zaa-margin-50);

}

.grid {
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 1fr 1fr;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  gap: 25px;
}


.tabContainer>* ul>li {
  display: flex;
  flex: 1;
}

.tabContainer>* ul>li:hover {
  background-color: var(--color-background-hover);
}

.tabContainer>* ul>li>a {
  flex: 1;
  text-align: center;
}

.tabContainer>* ul>li>.active {
  background: transparent !important;
  color: var(--color-main-text) !important;
  border-bottom: var(--default-grid-baseline) solid var(--color-primary-element) !important;
}

.tabContainer>* ul {
  display: flex;
  margin: 10px 8px 0 8px;
  justify-content: space-between;
  border-bottom: 1px solid var(--color-border);
}

.tabPanel {
  padding: 20px 10px;
  min-height: 100%;
  max-height: 100%;
  height: 100%;
  overflow: auto;
}
</style>
