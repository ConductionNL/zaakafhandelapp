<template>
	<div class="openZakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent :title="t('Geen open zaken')">
						<template #icon>
							<FolderIcon />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>
		<NcButton type="primary" @click="search">
			<template #icon>
				<OpenInApp :size="20" />
			</template>
			Zaken bekijken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import OpenInApp from 'vue-material-design-icons/OpenInApp.vue'

export default {
	name: 'OpenZakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		OpenInApp,
	},

	data() {
		return {
			loading: false,
			zaakItems: [
				{
					id: '1',
					mainText: 'Klant kan niet meer inloggen',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',
				},
				{
					id: '2',
					mainText: 'Klant heeft meerdere meldingen gemaakt',
					subText: 'Informatie verzoek',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},
				{
					id: '3',
					mainText: 'Geluidsoverlast',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},
				{
					id: '4',
					mainText: 'Klant kan niet meer inloggen',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',
				},
				{
					id: '5',
					mainText: 'Klant heeft meerdere meldingen gemaakt',
					subText: 'Informatie verzoek',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},
				{
					id: '6',
					mainText: 'Geluidsoverlast',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},
				{
					id: '7',
					mainText: 'Klant kan niet meer inloggen',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',
				},
				{
					id: '8',
					mainText: 'Klant heeft meerdere meldingen gemaakt',
					subText: 'Informatie verzoek',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},
				{
					id: '9',
					mainText: 'Geluidsoverlast',
					subText: 'Klacht',
					avatarUrl: '/apps-extra/zaakafhandelapp/img/briefcase-account-outline.svg',

				},

			],
		}
	},

	computed: {
		items() {
			return this.zaakItems
		},
	},

	mounted() {
	},

	methods: {
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style>
.openZakenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
   overflow: auto;
}
</style>
