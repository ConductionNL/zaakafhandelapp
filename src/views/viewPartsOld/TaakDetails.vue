<template>
	<NcAppContentDetails>
		<NcEmptyContent
			description="Nog geen taak geselecteerd">
			<template #icon>
				<CalendarMonthOutline />
			</template>
			<template #name>
				<h1 class="empty-content__name">
					Geen taak
				</h1>
			</template>
			<template #action>
				<NcButton type="primary">
					Taak aanmaken
				</NcButton>
			</template>
		</NcEmptyContent>
	</NcAppContentDetails>
</template>
<script>

import Vue from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import CalendarMonthOutline from 'vue-material-design-icons/CalendarMonthOutline';

import {
	NcAppSidebar,
	NcEmptyContent,
	NcButton,
	NcAppContentDetails
} from '@nextcloud/vue';


Vue.use(Loading);

export default {
	name: "TaakDetails",
	components: {
		NcAppSidebar,
		NcEmptyContent,
		CalendarMonthOutline,
		NcButton,
		NcAppContentDetails
	}
}
</script>

