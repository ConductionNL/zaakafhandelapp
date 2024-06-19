<template>
	<NcEmptyContent
		description="Nog geen klant geselecteerd">
		<template #icon>
			<AccountGroupOutline />
		</template>
		<template #name>
			<h1 class="empty-content__name">
				Geen klant
			</h1>
		</template>
		<template #action>
			<NcButton type="primary">
				Klant aanmaken
			</NcButton>
		</template>
	</NcEmptyContent>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import AccountGroupOutline from 'vue-material-design-icons/AccountGroupOutline';

import {
	NcEmptyContent,
	BriefcaseAccountOutline,
	NcButton
} from '@nextcloud/vue';


Vue.use(Loading);

export default {
	name: "KlantDetails",
	components: {
		NcEmptyContent,
		AccountGroupOutline,
		NcButton
	}
}
</script>

