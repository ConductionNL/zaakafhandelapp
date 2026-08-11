<!--
 ORPHANED LEGACY VIEW — this file is mounted by nothing.
 The manifest-v2 migration replaced it with the declarative "StatusDetail" page
 (/statussen/:id), which the library renderer draws through CnPageRenderer ->
 CnDetailPage. Verified: no module under src/ imports it, so webpack never
 bundles it and no browser can reach it.
 @visual exclude Dead file: superseded by the manifest-v2 "StatusDetail" page (/statussen/:id); imported by no module in src/, so it is in no bundle and no e2e can mount it. The live page is covered by tests/e2e/spec-coverage/ui-detail-views.spec.ts. Correct fix is deletion.
-->
<template>
	<div class="detailContainer">
		<div v-if="!loading" id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<h1 class="h1">
					{{ zaak.name }}
				</h1>
				<div class="grid">
					<div class="gridContent">
						<h4>{{ t('zaakafhandelapp', 'Summary:') }}</h4>
						<span>{{ zaak.summary }}</span>
					</div>
				</div>
			</div>
		</div>
		<NcLoadingIcon v-if="loading"
			:size="100"
			appearance="dark"
			:name="t('zaakafhandelapp', 'Loading case details')" />
	</div>
</template>

<script>
import { NcLoadingIcon } from '@nextcloud/vue'

export default {
	name: 'StatusDetails',
	components: {
		NcLoadingIcon,
	},
	props: {
		statusId: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			zaak: [],
			loading: false,
			statusItem: [],
		}
	},
	watch: {
		statusId: {
			/**
			 * @spec openspec/specs/ui-case-views/spec.md#REQ-005
			 */
			handler(statusId) {
				this.fetchData(statusId)
			},
			deep: true,
		},
	},
	mounted() {
		this.fetchData(this.statusItem)
	},
	methods: {
		/**
		 * @spec openspec/specs/ui-case-views/spec.md#REQ-001
		 */
		fetchData(statusId) {
			this.loading = true
			fetch(
				'/index.php/apps/zaakafhandelapp/api/zaken/' + statusId,
				{
					method: 'GET',
				},
			)
				.then((response) => {
					response.json().then((data) => {
						this.zaak = data
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
