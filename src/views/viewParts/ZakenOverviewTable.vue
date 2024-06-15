<template>
  <div class="tableContainer">
    <table class="vld-parent table" ref="table" :current-page="currentPage">
      <tr>
        <th>Zaaknummer</th>
        <th>Aanvrager</th>
        <th>Zaaktype</th>
        <th>Archief Status</th>
        <th>Vertrouwelijkheid</th>
        <th>Startdatum</th>
        <th></th>
      </tr>
      <tr v-for="(zaak, i) in zaken.results" :key="`${zaak}${i}`" class="table-rows">
        <td class="td">
          {{ zaak?.identificatie === "string" ? "ZAAK-2019-183641313" : (zaak?.identificatie ?? "Onbekend") }}
        </td>
        <td class="td">
          {{ zaak?.bronorganisatie !== "string" ? zaak?.bronorganisatie : "Onbekend" }}
        </td>
        <td class="td">
          {{ zaak?.zaaktype === "http://localhost/api/ztc/v1/zaaktypen/a1748dd6-50a3-464d-b95e-554e87298ce9" ?
            "Aanmelding hondenbelasting" : (zaak?.zaaktype ?? "Onbekend") }}
        </td>
        <td class="td">
          {{ zaak?.archiefstatus ?? "Onbekend" }}
        </td>
        <td class="td">
          {{ zaak?.vertrouwelijkheidaanduiding ?? "Onbekend" }}
        </td>
        <td class="td">
          {{ zaak?.startdatum ?? "Onbekend" }}
        </td>
        <td class="td">
          <a class="link" :href="'/index.php/apps/dsonextcloud/zaken/' + zaak.id">Details</a>
        </td>
      </tr>
    </table>

    <b-pagination class="pagination" @page-click="onPageClick" v-model="currentPage" :total-rows="zaken?.count"
      :per-page="perPageLimit"></b-pagination>

  </div>
</template>
<script>
import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { BPaginationNav, BPagination } from 'bootstrap-vue'
Vue.component('b-pagination-nav', BPaginationNav)
Vue.component('b-pagination', BPagination)

Vue.use(Loading);

export default {
  name: "ZakenOverviewTable",
  props: {
    msg: String,
  },
  data() {
    return {
      zaken: '',
      fullPage: false,
      currentPage: 1,
      perPageLimit: 10,
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    fetchData(newPage) {
      let loader = this.$loading.show({
        container: this.fullPage ? null : this.$refs.table,
        canCancel: true,
        onCancel: this.onCancel,
        color: "#39870c",
        loader: "dots",
      });
      fetch(
        'https://api.test.common-gateway.commonground.nu/api/zrc/v1/zaken?' + new URLSearchParams({ page: newPage ?? this.currentPage, _limit: this.perPageLimit }),
        {
          method: 'GET',
          headers: {
            "Authorization": "2877fe72-89a4-412a-af44-722899494117"
          }
        },
      )
        .then((response) => {
          response.json().then((data) => {
            this.zaken = data
          })
          loader.hide()
        })
        .catch((err) => {
          console.error(err)
          loader.hide()
        })
    },
    onPageClick(event, page) {
      this.fetchData(page)
    }
  },
}
</script>
<style>
.tableContainer {
  padding: 24px
}

.table {
  min-height: 200px;
  border-collapse: collapse;
  width: 100%;
  table-layout: auto !important;
  word-wrap: break-word;
  margin-block-end: 24px;
}

.td {
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

.pagination {
  display: flex;
  justify-content: center;
  gap: 10px;
  align-items: center;
}

.link {
  color: #1a0dab
}

.link:hover {
  cursor: pointer;
  color: #1a0dab;
  text-decoration: underline;
}
</style>
