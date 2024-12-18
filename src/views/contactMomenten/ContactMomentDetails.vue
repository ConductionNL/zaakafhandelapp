<script setup>
import { navigationStore, contactMomentStore } from '../../store/store.js'
</script>

<template>
	<div class="detailContainer">
		<div id="app-content">
			<!-- app-content-wrapper is optional, only use if app-content-list  -->
			<div>
				<div class="head">
					<h1 class="h1">
						{{ contactMomentStore.contactMomentItem.titel }}
					</h1>

					<NcActions :primary="true" menu-name="Acties">
						<template #icon>
							<DotsHorizontal :size="20" />
						</template>
						<NcActionButton @click="navigationStore.setModal('contactMomentenForm')">
							<template #icon>
								<Pencil :size="20" />
							</template>
							Bewerken
						</NcActionButton>
						<NcActionButton @click="closeContactMoment">
							<template #icon>
								<ProgressClose :size="20" />
							</template>
							Sluiten
						</NcActionButton>
						<NcActionButton @click="navigationStore.setModal('deleteContactMoment')">
							<template #icon>
								<TrashCanOutline :size="20" />
							</template>
							Verwijderen
						</NcActionButton>
					</NcActions>
				</div>
				<div class="detailGrid">
					<div>
						<b>Notitie:</b>
						<p>{{ contactMomentStore.contactMomentItem.notitie }}</p>
					</div>
					<div>
						<b>Start datum:</b>
						<p>{{ new Date(contactMomentStore.contactMomentItem.startDate).toLocaleString() }}</p>
					</div>
					<div>
						<b>Klant:</b>
						<p>{{ contactMomentStore.contactMomentItem.klant }}</p>
					</div>
					<div>
						<b>Zaak:</b>
						<p>{{ contactMomentStore.contactMomentItem.zaak }}</p>
					</div>
					<div>
						<b>Taak:</b>
						<p>{{ contactMomentStore.contactMomentItem.taak }}</p>
					</div>
					<div>
						<b>Product:</b>
						<p>{{ contactMomentStore.contactMomentItem.product }}</p>
					</div>
					<div>
						<b>Status:</b>
						<p>{{ contactMomentStore.contactMomentItem.status }}</p>
					</div>
				</div>

				<div class="tabContainer">
					<BTabs content-class="mt-3" justified>
						<BTab title="Audit trail" active>
							<div v-if="auditTrails.length">
								<NcListItem v-for="(auditTrail, key) in auditTrails"
									:key="key"
									:name="new Date(auditTrail.created).toLocaleString()"
									:bold="false"
									:details="auditTrail.action"
									:counter-number="Object.keys(auditTrail.changed).length"
									:force-display-actions="true">
									<template #icon>
										<TimelineQuestionOutline disable-menu :size="44" />
									</template>
									<template #subname>
										{{ auditTrail.userName }}
									</template>
									<template #actions>
										<NcActionButton
											@click="contactMomentStore.setAuditTrailItem(auditTrail); navigationStore.setModal('viewContactMomentAuditTrail')">
											<template #icon>
												<Eye :size="20" />
											</template>
											View details
										</NcActionButton>
									</template>
								</NcListItem>
							</div>
							<NcEmptyContent v-else icon="icon-history" title="Geen audit trail gevonden">
								<template #description>
									Er is geen audit trail gevonden voor dit contactmoment.
								</template>
							</NcEmptyContent>
						</BTab>
					</BTabs>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// Components
import { NcActions, NcActionButton, NcListItem, NcEmptyContent } from '@nextcloud/vue'
import { BTabs, BTab } from 'bootstrap-vue'

// Entities
import { ContactMoment } from '../../entities/index.js'

// Icons
import DotsHorizontal from 'vue-material-design-icons/DotsHorizontal.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import TimelineQuestionOutline from 'vue-material-design-icons/TimelineQuestionOutline.vue'
import ProgressClose from 'vue-material-design-icons/ProgressClose.vue'

export default {
	name: 'ContactMomentDetails',
	components: {
		// Components
		NcActions,
		NcActionButton,
		// Icons
		Pencil,
		DotsHorizontal,
		TrashCanOutline,
	},
	data() {
		return {
			currentActiveContactMoment: null,
			auditTrails: [],
		}
	},
	mounted() {
		if (contactMomentStore.contactMomentItem?.id) {
			this.currentActiveContactMoment = contactMomentStore.contactMomentItem
			// this.fetchAuditTrails(contactMomentStore.contactMomentItem.id)
		}
	},
	updated() {
		if (contactMomentStore.contactMomentItem?.id && JSON.stringify(this.currentActiveContactMoment) !== JSON.stringify(contactMomentStore.contactMomentItem)) {
			this.currentActiveContactMoment = contactMomentStore.contactMomentItem
			// this.fetchAuditTrails(contactMomentStore.contactMomentItem.id)
		}
	},
	methods: {
		fetchAuditTrails(id) {
			fetch(`/index.php/apps/zaakafhandelapp/api/contact_momenten/${id}/audit_trail`)
				.then(response => response.json())
				.then(data => {
					if (Array.isArray(data)) {
						this.auditTrails = data
					}
				})
		},
		async closeContactMoment() {
			if (contactMomentStore.contactMomentItem?.status === 'gesloten') {
				console.info('Contact moment is already closed')
				return
			}

			const newContactMoment = new ContactMoment({
				...contactMomentStore.contactMomentItem,
				status: 'gesloten',
			})

			contactMomentStore.saveContactMoment(newContactMoment)
				.then(({ response }) => {
					if (response.ok) {
						this.fetchContactMomentItems()
					}
				})
		},
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

.tabPanel {
	padding: 20px 10px;
	min-height: 100%;
	max-height: 100%;
	height: 100%;
	overflow: auto;
}
</style>
