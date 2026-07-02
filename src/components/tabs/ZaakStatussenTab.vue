<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Sidebar tab: statuses for a Zaak (case).

 STUB — placeholder for the manifest-v1 migration. The full
 implementation reuses the existing `zaakStore.statussen` collection
 plus the `updateZaakStatus` modal flow; landing in a sibling change
 once runtime regression confirms the manifest dispatcher.

 TODO(zaakafhandelapp-manifest-v1): replace placeholder with a
 CnDataTable rendering the case's status timeline plus a "Change
 status" CTA opening the existing modal.
-->
<template>
	<CnNoteCard type="info" :title="t('zaakafhandelapp', 'Statuses')">
		{{ t('zaakafhandelapp', 'Status timeline for this case will appear here once the manifest renderer is wired to the existing zaakStore.statussen collection.') }}
	</CnNoteCard>
</template>

<script>
import { CnNoteCard } from '@conduction/nextcloud-vue'

export default {
	name: 'ZaakStatussenTab',
	components: { CnNoteCard },
}
</script>
