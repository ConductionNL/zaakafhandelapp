<template>
	<NcEmptyContent
		description="Nog geen zaak type geselecteerd">
		<template #icon>
			<BriefcaseOutline />
		</template>
		<template #name>
			<h1 class="empty-content__name">
				Geen zaak type
			</h1>
		</template>
		<template #action>
			<NcButton type="primary">
				Zaak type aanmaken
			</NcButton>
		</template>
	</NcEmptyContent>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import BriefcaseOutline from 'vue-material-design-icons/BriefcaseOutline';

import {
	NcEmptyContent,
	NcButton
} from '@nextcloud/vue';


Vue.use(Loading);

export default {
	name: "ZaakTypeDetails",
	components: {
		NcEmptyContent,
		BriefcaseOutline,
		NcButton
	}
}
</script>

