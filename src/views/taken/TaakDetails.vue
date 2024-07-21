<script setup>
import { store } from '../../store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ store.taakItem.title }}
					</h1>
					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="editTaak(taak)">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
					</NcActions>
				</div>
				<div class="grid">
					<div class="gridContent">
						<b>Sammenvatting:</b>
						<span>{{ store.taakItem.onderwerp }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { NcLoadingIcon, NcActions, NcActionButton } from '@nextcloud/vue'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'

export default {
	name: 'TaakDetails',
	components: {
		NcLoadingIcon,
	},
}
</script>

<style>
h4 {
  font-weight: bold
}

.h1 {
  display: block !important;
  font-size: 2em !important;
  margin-block-start: 0.67em !important;
  margin-block-end: 0.67em !important;
  margin-inline-start: 0px !important;
  margin-inline-end: 0px !important;
  font-weight: bold !important;
  unicode-bidi: isolate !important;
}

.grid {
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 1fr 1fr;
  margin-block-start: var(--zaa-margin-50);
  margin-block-end: var(--zaa-margin-50);
}

.gridContent {
  display: flex;
  gap: 25px;
}

</style>
