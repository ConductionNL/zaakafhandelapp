<template>
	<NcEmptyContent
		description="Nog geen contact moment geselecteerd">
		<template #icon>
			<ChatOutline />
		</template>
		<template #name>
			<h1 class="empty-content__name">
				Geen contact moment
			</h1>
		</template>
		<template #action>
			<NcButton type="primary">
				Contact moment aanmaken
			</NcButton>
		</template>
	</NcEmptyContent>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import ChatOutline from 'vue-material-design-icons/ChatOutline';

import {
	NcEmptyContent,
	NcButton
} from '@nextcloud/vue';


Vue.use(Loading);

export default {
	name: "ContactMomentDetails",
	components: {
		NcEmptyContent,
		ChatOutline,
		NcButton
	}
}
</script>

