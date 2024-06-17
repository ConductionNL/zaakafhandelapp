<template>
	<div class="app-content-list">
		<a class="app-content-list-item" v-for="(zaak, i) in zaken.results" :key="`${zaak}${i}`" :href="'/index.php/apps/dsonextcloud/zaken/' + zaak.id">

			<div class="app-content-list-item-star icon-starred"></div>
			<div class="app-content-list-item-icon" style="background-color: rgb(41, 97, 156);">N</div>
			<div class="app-content-list-item-line-one">{{ zaak?.omschrijving ?? "Onbekend" }}</div>
			<div class="app-content-list-item-line-two">{{ zaak?.zaaktype ?? "Onbekend" }}</div>
			<span class="app-content-list-item-details">{{ zaak?.einddatumGepland ?? "Onbekend" }}</span>
			<div class="icon-more"></div>
		</a>
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
