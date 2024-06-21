<template>
  <NcAppContentList>
    <ul>
      <div class="listHeader">
        <NcTextField class="searchField" disabled :value.sync="search" label="Search" trailing-button-icon="close"
          :show-trailing-button="search !== ''" @trailing-button-click="clearText">
          <Magnify :size="20" />
        </NcTextField>
      </div>

      <NcListItem v-for="(zaak, i) in zaken.results" :key="`${zaak}${i}`" :name="zaak?.omschrijving" :bold="false"
        :force-display-actions="true" :active="activeZaak === zaak.id" :details="'1h'" :counter-number="44"
        @click="setActive(zaak.id)">

        <template #icon>
          <BriefcaseOutline :class="activeZaak === zaak.id && 'selectedZaakIcon'" disable-menu :size="44" user="janedoe"
            display-name="Jane Doe" />
        </template>
        <template #subname>
          {{ zaak?.zaaktype === 'http://localhost/api/ztc/v1/zaaktypen/a1748dd6-50a3-464d-b95e-554e87298ce9' ?
            'Aanmelding hondenbelasting' : (zaak?.zaaktype ?? 'Onbekend') }}
        </template>
        <template #actions>
          <NcActionButton>
            Button one
          </NcActionButton>
          <NcActionButton>
            Button two
          </NcActionButton>
          <NcActionButton>
            Button three
          </NcActionButton>
        </template>
      </NcListItem>

      <div v-if="loading">
        <div class="skeletonContainer">
          <VueSkeletonLoader type="circle" :size="44" animation="fade" />

          <div class="skeletonParagraph">
            <VueSkeletonLoader type="rectangle" :width="200" :height="15" animation="fade" rounded />
            <VueSkeletonLoader class="skeletonParagraphLastChild" type="rectangle" :width="165" :height="15"
              animation="fade" rounded />
          </div>
        </div>

        <div class="skeletonContainer">
          <VueSkeletonLoader type="circle" :size="44" animation="fade" />

          <div class="skeletonParagraph">
            <VueSkeletonLoader type="rectangle" :width="200" :height="15" animation="fade" rounded />
            <VueSkeletonLoader class="skeletonParagraphLastChild" type="rectangle" :width="165" :height="15"
              animation="fade" rounded />
          </div>
        </div>
      </div>
    </ul>
  </NcAppContentList>
</template>
<script>
import { TEMP_AUTHORIZATION_KEY } from '../../data/TempAuthKey';
import { NcListItem, NcListItemIcon, NcActionButton, NcAvatar, NcAppContentList, NcTextField } from '@nextcloud/vue';
import Magnify from 'vue-material-design-icons/Magnify';
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline';
import VueSkeletonLoader from 'skeleton-loader-vue';

export default {
  name: "ZakenList",
  components: {
    NcListItem,
    NcListItemIcon,
    NcActionButton,
    NcAvatar,
    NcAppContentList,
    NcTextField,
    BriefcaseOutline,
    Magnify,
    VueSkeletonLoader
  },
  data() {
    return {
      zaken: '',
      search: '',
      activeZaak: '',
      fullPage: false,
      currentPage: 1,
      perPageLimit: 10,
      loading: false,
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    fetchData(newPage) {
      this.loading = true,
        fetch(
          'https://api.test.common-gateway.commonground.nu/api/zrc/v1/zaken?' + new URLSearchParams({ page: newPage ?? this.currentPage, _limit: this.perPageLimit }),
          {
            method: 'GET',
            headers: {
              "Authorization": TEMP_AUTHORIZATION_KEY
            }
          },
        )
          .then((response) => {
            response.json().then((data) => {
              this.zaken = data
            })
            this.loading = false
          })
          .catch((err) => {
            console.error(err)
            this.loading = false
          })
    },
    setActive(id) {
      this.activeZaak = id
      this.$emit('activeZaak', true)
      this.$emit('activeZaakId', id)
    },
    clearText() {
      this.search = ''
    }
  },
}
</script>
<style>
.listHeader {
  position: sticky;
  top: 0;
  z-index: 1000;
  background-color: var(--color-main-background);
  border-bottom: 1px solid var(--color-border);
}

.searchField {
  padding-inline-start: 65px;
  padding-inline-end: 20px;
  margin-block-end: 6px;
}

.selectedZaakIcon>svg {
  fill: white;
}

.skeletonContainer {
  display: flex;
  padding: 8px 10px;
  margin: 4px;
}

.skeletonParagraph {
  align-content: center;
  margin-inline-start: 10px;

}

.skeletonParagraphLastChild {
  margin-block-start: 3px;
}
</style>
