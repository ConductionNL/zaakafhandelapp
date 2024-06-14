<template>
  <div class="container">
    <button @click="fetchData">Reload</button>
    <table class="vld-parent table" ref="table">
      <tr>
        <th>Naam</th>
        <th>SoftwareType</th>
        <th>Laag</th>
      </tr>
      <tr v-for="(component, i) in components" :key="`${component}${i}`" class="table-rows">
        <td>
          {{ component.name }}
        </td>
        <td>
          {{ component.softwareType }}
        </td>
        <td>
          {{ component?.embedded?.nl?.embedded?.commonground?.layerType }}
        </td>
      </tr>
    </table>
  </div>
</template>
<script>
import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
Vue.use(Loading);

export default {
  name: "SamenwerkingOverviewTable",
  props: {
    msg: String,
  },
  data() {
    return {
      components: '',
      fullPage: false,
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      let loader = this.$loading.show({
        container: this.fullPage ? null : this.$refs.table,
        canCancel: true,
        onCancel: this.onCancel,
        color: "#39870c",
        loader: "dots",
      });
      fetch(
        'https://api.opencatalogi.nl/api/search?page=1&limit=10&extend[]=all&isBasedOn=IS%20NULL&order[embedded.rating.rating]=desc&embedded.rating.rating[%3E%3D]=16&developmentStatus=stable',
        {
          method: 'GET',
        },
      )
        .then((response) => {
          response.json().then((data) => {
            this.components = data.results
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
button {
  padding: 12px 32px;
  font-size: 16px;
  border-radius: 8px;
}

.table {
  min-height: 200px;
}

table {
  border-collapse: collapse;
  width: 100%;
  table-layout: auto !important;
  word-wrap: break-word;
}

td {
  padding: 24px;
  border-bottom: 1px solid rgb(224, 242, 237);
}

.header-item {
  padding: 30px 20px;
  font-size: 12px;
  background-color: rgb(224, 242, 237);
  text-transform: uppercase;
}

.table-rows:nth-child(odd) {
  background-color: rgb(250, 250, 250);
}

.table-rows:nth-child(n):hover {
  background-color: rgb(244, 246, 245);
}

.container {
  background-color: purple;
  margin-block-start: 75px;
  margin-inline-end: auto;
  margin-inline-start: auto;
  max-inline-size: calc(1140px - 24px - 24px);
  padding-inline-end: 24px;
  padding-inline-start: 24px;
}
</style>