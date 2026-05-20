// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// 5-kind component registry (v2 manifest pattern per hydra ADR-036).
//
// Manifest pages are all typed primitives now (index / detail / dashboard
// / logs / settings) — see issue #234 for the Search + AuditTrail
// elimination. The only consumer-injected components left are:
//   - SettingsForm — settings section body (lib gap; future split into
//     per-section widgets)
//   - Zaak*Tab     — ZaakDetail sidebar tabs that span schemas
//                    (cross-register relations, no abstract widget yet)

import SettingsForm from './views/settings/Settings.vue'
import ZaakTakenTab from './components/tabs/ZaakTakenTab.vue'
import ZaakRollenTab from './components/tabs/ZaakRollenTab.vue'
import ZaakDocumentenTab from './components/tabs/ZaakDocumentenTab.vue'
import ZaakBesluitenTab from './components/tabs/ZaakBesluitenTab.vue'
import ZaakBerichtenTab from './components/tabs/ZaakBerichtenTab.vue'
import ZaakResultatenTab from './components/tabs/ZaakResultatenTab.vue'
import ZaakStatussenTab from './components/tabs/ZaakStatussenTab.vue'

export default {
	SettingsForm: { kind: 'section', component: SettingsForm },
	ZaakTakenTab: { kind: 'tab', component: ZaakTakenTab },
	ZaakRollenTab: { kind: 'tab', component: ZaakRollenTab },
	ZaakDocumentenTab: { kind: 'tab', component: ZaakDocumentenTab },
	ZaakBesluitenTab: { kind: 'tab', component: ZaakBesluitenTab },
	ZaakBerichtenTab: { kind: 'tab', component: ZaakBerichtenTab },
	ZaakResultatenTab: { kind: 'tab', component: ZaakResultatenTab },
	ZaakStatussenTab: { kind: 'tab', component: ZaakStatussenTab },
}
