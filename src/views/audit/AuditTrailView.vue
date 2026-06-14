<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Placeholder for the legacy `/auditTrail` route.

 The original navigation entry in src/navigation/MainMenu.vue had no
 view binding under the legacy router. This stub keeps the menu entry
 functional under the manifest dispatcher; the real audit page lands
 in a sibling change.

 TODO(zaakafhandelapp-manifest-v1): wire to OpenRegister's audit-trail
 endpoint once the in-app register slug is decided.
-->
<template>
	<NcAppContent>
		<div class="zaa-audit-placeholder">
			<CnNoteCard type="info" :title="t('zaakafhandelapp', 'Audit trail')">
				{{ t('zaakafhandelapp', 'The cross-app audit trail view will land in a follow-up change.') }}
			</CnNoteCard>
		</div>
	</NcAppContent>
</template>

<script>
import { NcAppContent } from '@nextcloud/vue'
import { CnNoteCard } from '@conduction/nextcloud-vue'

export default {
	name: 'AuditTrailView',
	components: { NcAppContent, CnNoteCard },
}
</script>

<style scoped>
.zaa-audit-placeholder {
	padding: 24px;
}
</style>
