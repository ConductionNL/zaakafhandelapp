<template>
  <div class="container">
    <div id="app-navigation" class="">
      <div class="app-navigation-new">
        <button type="button" class="icon-add">
          Zaak Aanmaken
        </button>
      </div>

      <!-- Your navigation here -->
      <ul id="usergrouplist">
        <li class="app-navigation-entry active">
          <a class="app-navigation-entry-link" href="/index.php/apps/dsonextcloud/zaken">Zaken</a>
          <div class="app-navigation-entry-utils">
            <ul>
              <li class="app-navigation-entry-utils-counter"></li>
            </ul>
          </div>
        </li>
        <li>
          <a href="/index.php/apps/dsonextcloud/taken">Taken</a>
        </li>
        <li><a href="#">Klanten</a></li>
        <li>
          <a href="#">Contact Momenten</a>
          <ul>
            <li><a href="#">Second level entry</a></li>
            <li><a href="#">Second level entry</a></li>
          </ul>
        </li>
      </ul>

      <div id="app-settings">
        <!-- app settings -->
        <div id="app-settings-header">
          <button class="settings-button" data-apps-slide-toggle="#app-settings-content">
            Settings
          </button>
        </div>
        <div id="app-settings-content">
          <div class="app-navigation-new">
            <ul>
              <li><a href="#">Zaak Typen</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>



    <div id="app-content">
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
  </div>
</template>
<script>
import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

Vue.use(Loading);

export default {
  name: "ZakenDetail",
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
            "Authorization": "2877fe72-89a4-412a-af44-722899494117"
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
