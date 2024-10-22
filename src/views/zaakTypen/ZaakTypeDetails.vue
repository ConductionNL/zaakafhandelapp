<script setup>
import { navigationStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<h1 class="h1">
					{{ zaaktype.name }}
				</h1>
				<div class="grid">
					<div class="gridContent">
						<h4>Sammenvatting:</h4>
						<span>{{ zaaktype.summary }}</span>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			name="Zaak type details aan het laden" />
	</div>
</template>

<script>
import { NcLoadingIcon } from '@nextcloud/vue'

export default {
	name: 'ZaakTypeDetails',
	components: {
		NcLoadingIcon,
	},
	props: {
		zaaktypeId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			zaaktype: [],
			loading: false,
		}
	},
	watch: {
		zaaktypeId: {
			handler(zaaktypeId) {
				this.fetchData(zaaktypeId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(store.zaakTypeItem)
	},
	methods: {
		fetchData(zaaktypeId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/ztc/zaaktypen/' + zaaktypeId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zaaktype = data
					})
					this.loading = false
				})
				.catch((err) => {
					console.error(err)
					this.loading = false
				})
		},
	},
}
</script>

<style>
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

</style>
