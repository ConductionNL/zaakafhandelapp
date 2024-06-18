<template>
	<NcContent appName="dsonextcloud">
		<Navigation selected="taken" />
		<NcAppContent>
			<template #list>
				<TakenList />
			</template>
			<template #default>
				<TaakDetails />
			</template>
		</NcAppContent>
		<TaakSidebar />
	</NcContent>
</template>
<script>

import TakenList from './viewParts/TakenList.vue';
import Navigation from './viewParts/Navigation.vue';
import TaakDetails from './viewParts/TaakDetails.vue';
import TaakSidebar from './viewParts/TaakSidebar.vue';
import { NcAppContent,NcContent } from '@nextcloud/vue';

export default {
	name: "app",
	components: {
		TakenList,
		Navigation,
		TaakDetails,
		TaakSidebar,
		NcAppContent,
		NcContent
	}

}
</script>
