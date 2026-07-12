<!-- SPDX-License-Identifier: EUPL-1.2 -->
<!-- Copyright (C) 2026 Conduction B.V. -->

<!--
 Sidebar tab: tasks for a Zaak (case).

 STUB — placeholder for the manifest-v1 migration. The full
 implementation reuses the existing `taakStore` + `AddTaakToZaak.vue`
 modal flow; landing in a sibling change once runtime regression
 confirms the manifest dispatcher.

 TODO(zaakafhandelapp-manifest-v1): replace placeholder with a
 CnDataTable bound to `taakStore.takenList` filtered by
 `zaakId === parent.id`, plus the existing modal triggers.
-->
<template>
	<CnNoteCard type="info" :title="t('zaakafhandelapp', 'Tasks')">
		{{ t('zaakafhandelapp', 'Task list for this case will appear here once the manifest renderer is wired to the existing taakStore.') }}
	</CnNoteCard>
</template>

<script>
import { CnNoteCard } from '@conduction/nextcloud-vue'

export default {
	name: 'ZaakTakenTab',
	components: { CnNoteCard },
}
</script>
