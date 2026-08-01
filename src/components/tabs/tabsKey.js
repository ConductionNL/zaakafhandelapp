// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Injection key shared by Tabs.vue (provider) and Tab.vue (consumer).
//
// It lives in its own module rather than being a named export of Tabs.vue so
// that both SFCs import the SAME symbol instance. A `Symbol()` created inside
// an SFC's `<script>` block is module-local, and a package that ends up loaded
// twice (CJS + ESM, or via two different aliases) silently yields two distinct
// symbols — `inject()` then falls through to the default and the component
// degrades to "no parent" with no error at all. Same failure shape as
// `useTenantContext` in @conduction/nextcloud-vue.

export const TABS_INJECTION_KEY = Symbol('zaakafhandelapp:tabs')
