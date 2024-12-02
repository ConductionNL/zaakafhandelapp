<script setup>
import { zaakStore, navigationStore } from '../../store/store.js'
</script>

<template>
	<NcModal label-id="View Zaak Audit Trail modal"
		@close="closeDialog">
		<div class="modal__content">
			<div class="audit-item">
				<h3>Audit Trail ID: {{ auditTrail.id }}</h3>

				<div class="audit-item-details">
					<p><strong>Action:</strong> {{ auditTrail.action }}</p>
					<p><strong>User:</strong> {{ auditTrail.userName }} ({{ auditTrail.user }})</p>
					<p><strong>Session:</strong> {{ auditTrail.session }}</p>
					<p><strong>IP Address:</strong> {{ auditTrail.ipAddress }}</p>
					<p><strong>Created:</strong> {{ new Date(auditTrail.created).toLocaleString() }}</p>
				</div>

				<div v-if="auditTrail.changed" class="audit-trail-changes">
					<h4>Changes:</h4>
					<div class="audit-trail-table-container">
						<table class="audit-trail-table">
							<thead>
								<tr>
									<th>Field</th>
									<th>Old Value</th>
									<th>New Value</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(change, key) in auditTrail.changed" :key="key">
									<td><strong>{{ key }}</strong></td>
									<td>{{ formatValue(change.old) }}</td>
									<td>{{ formatValue(change.new) }}</td>
								</tr>
							</tbody>
						</table>
					</div>
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
	name: 'ViewZaakAuditTrail',
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
		// Assuming zaakStore.auditTrailItem is a single audit trail object
		this.auditTrail = zaakStore.auditTrailItem || {}
	},
	methods: {
		closeDialog() {
			navigationStore.setModal(null)
			zaakStore.setAuditTrailItem(null)
		},
		formatValue(value) {
			if (value === null || value === undefined) {
				return 'N/A' // Handle null or undefined
			} else if (typeof value === 'object') {
				return JSON.stringify(value, null, 2) // Format JSON objects
			} else if (typeof value === 'boolean') {
				return value ? 'True' : 'False' // Format booleans
			}
			return value // Return the value as is for other types
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
.audit-item > *:not(:last-child) {
	margin-bottom: 1rem;
}

.navigation-buttons {
	margin-top: 10px;
	display: flex;
	gap: 10px;
	justify-content: center;
}

/* Changes Table */
.audit-trail-table-container {
	max-height: 350px;
	overflow-y: auto;
}

.audit-trail-table thead {
	position: sticky;
	top: 0;
	background-color: var(--color-main-background);
}

.audit-trail-table th {
	font-weight: bold;
	font-size: 1rem;
}

.audit-trail-table th,
.audit-trail-table td {
	padding: 0.5rem;
}
</style>
