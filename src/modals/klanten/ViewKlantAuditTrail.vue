<script setup>
import { klantStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal label-id="View Klant Audit Trail modal"
		@close="closeDialog">
		<div class="modal__content">
			<div class="audit-item">
				<h3>Audit Trail ID: {{ auditTrail.id }}</h3>

				<p><strong>Action:</strong> {{ auditTrail.action }}</p>
				<p><strong>User:</strong> {{ auditTrail.userName }} ({{ auditTrail.user }})</p>
				<p><strong>Session:</strong> {{ auditTrail.session }}</p>
				<p><strong>IP Address:</strong> {{ auditTrail.ipAddress }}</p>
				<p><strong>Created:</strong> {{ new Date(auditTrail.created).toLocaleString() }}</p>

				<div v-if="auditTrail.changed">
					<h4>Changes:</h4>
					<ul>
						<li v-for="(change, key) in auditTrail.changed" :key="key">
							<strong>{{ key }}:</strong><br>
							<span>Old: {{ change.old ?? 'N/A' }}</span><br>
							<span>New: {{ change.new ?? 'N/A' }}</span>
						</li>
					</ul>
				</div>
			</div>

			<NcButton @click="closeDialog">
				<template #icon>
					<Cancel :size="20" />
				</template>
				Close
			</NcButton>
		</div>
	</NcModal>
</template>

<script>
import {
	NcModal,
	NcButton,
} from '@nextcloud/vue'

import Cancel from 'vue-material-design-icons/Cancel.vue'

export default {
	name: 'ViewKlantAuditTrail',
	components: {
		NcModal,
		NcButton,
		Cancel,
	},
	data() {
		return {
			auditTrail: {}, // Initialize with an empty object
		}
	},
	mounted() {
		// Assuming klantStore.auditTrailItem is a single audit trail object
		this.auditTrail = klantStore.auditTrailItem || {}
	},
	methods: {
		closeDialog() {
			navigationStore.setModal(null)
			klantStore.setAuditTrailItem(null)
		},
	},
}
</script>

<style scoped>
.modal__content {
	margin: 0.8rem;
}

.audit-item {
	border-bottom: 1px solid #ccc;
	padding: 0 0 10px 0;
	margin: 0 0 10px 0;
}

.navigation-buttons {
	margin-top: 10px;
	display: flex;
	gap: 10px;
	justify-content: center;
}
</style>
