<template>
	<NcContent appName="dsonextcloud">
		<Navigation selected="zaken" />
		<NcAppContent>
			<template #list>
				<ZakenList @activeZaak="updateActiveZaak" @activeZaakId="updateActiveZaakId" />
			</template>
			<template #default>
				<ZaakDetails v-if="activeZaak !== true" />
				<ZakenDetail :zaakId="activeZaakId" v-if="activeZaak === true && activeZaakId" />
			</template>
		</NcAppContent>
		<!-- <ZaakSidebar /> -->
	</NcContent>
</template>

<script>
import ZakenList from './viewParts/ZakenList.vue';
import Navigation from './viewParts/Navigation.vue';
import ZaakDetails from './viewParts/ZaakDetails.vue';
import ZakenDetail from './ZakenDetail.vue';
import ZaakSidebar from './viewParts/ZaakSidebar.vue';
import { NcAppContent, NcContent } from '@nextcloud/vue';

export default {
	name: "app",
	components: {
		ZakenList,
		Navigation,
		ZaakDetails,
		ZakenDetail,
		ZaakSidebar,
		NcAppContent,
		NcContent
	},
	data() {
		return {
			activeZaak: false,
			activeZaakId: '',
		}
	},
	methods: {
		updateActiveZaak(variable) {
			this.activeZaak = variable
		},
		updateActiveZaakId(variable) {
			this.activeZaakId = variable
		}
	}
}
</script>
