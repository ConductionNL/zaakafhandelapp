<template>
	<div class="zakenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				:item-menu="itemMenu"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent :title="t('Geen open zaken')">
						<template #icon>
							<Folder />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<NcButton type="primary" @click="search">
			<template #icon>
				<Plus :size="20" />
			</template>
			Zaak aanmaken
		</NcButton>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton } from '@nextcloud/vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Folder from 'vue-material-design-icons/Folder.vue'

export default {
	name: 'ZakenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Plus,
		Folder,
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
.zakenContainer{
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}
.itemContainer{
	overflow: auto;
 }
</style>
