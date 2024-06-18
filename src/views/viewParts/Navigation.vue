<template>
	<div>
		<NcAppNavigation>
			<NcAppNavigationList>
				<NcAppNavigationNewItem name="Zaak Aanmaken" icon="" @new-item="function (value) { alert(value) }">
					<template #icon>
						<Plus :size="20" />
					</template>
				</NcAppNavigationNewItem>
				<NcAppNavigationItem name="Zaken" href="/index.php/apps/dsonextcloud/zaken" />
				<NcAppNavigationItem name="Taken" href=" /index.php/apps/dsonextcloud/taken" />
				<NcAppNavigationItem name="Klanten" href=" /index.php/apps/dsonextcloud/klanten" />
				<NcAppNavigationItem name="Contact Momenten" href=" /index.php/apps/dsonextcloud/contact_momenten" />
			</NcAppNavigationList>
			<NcAppNavigationSettings>
				<NcAppNavigationItem name="Zaak Typen" href=" /index.php/apps/dsonextcloud/zaak_typen" />
				<NcAppNavigationItem name="Configuration" href=" /index.php/apps/dsonextcloud/configuration" />

			</NcAppNavigationSettings>
		</NcAppNavigation>
	</div>
</template>
<script>
import Vue from 'vue';
import { NcAppNavigation, NcAppNavigationList, NcAppNavigationItem, NcAppNavigationNewItem, NcAppNavigationSettings } from '@nextcloud/vue';
import Plus from 'vue-material-design-icons/Plus'

Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

export default {
	name: "Navigation",
	components: {
		NcAppNavigation,
		NcAppNavigationList,
		NcAppNavigationItem,
		NcAppNavigationNewItem,
		NcAppNavigationSettings,
		Plus
	}

}
</script>
<style></style>