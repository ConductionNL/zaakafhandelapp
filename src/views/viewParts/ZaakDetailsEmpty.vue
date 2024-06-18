<template>
	<NcEmptyContent
		name = "Geen zaak"
		description="Nog geen zaak geselecteerd">
		<template #icon>
			<BriefcaseAccountOutline />
		</template>
		<template #action>
			<NcButton type="primary">
				Zaak aanmaken
			</NcButton>
		</template>
	</NcEmptyContent>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import BriefcaseAccountOutline from 'vue-material-design-icons/BriefcaseAccountOutline';

import {
	NcEmptyContent,
	NcButton
} from '@nextcloud/vue';


Vue.use(Loading);

export default {
	name: "ZaakDetails",
	components: {
		NcEmptyContent,
		BriefcaseAccountOutline,
		NcButton
	}
}
</script>



