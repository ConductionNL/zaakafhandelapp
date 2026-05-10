// SPDX-License-Identifier: EUPL-1.2
// Copyright (C) 2026 Conduction B.V.
//
// Test-only stub of @nextcloud/browser-storage.
//
// The real package ships pure ESM with `"type": "module"`. Babel-jest can't
// transparently transform it from a CJS dependency chain (it gets pulled in
// transitively via @nextcloud/auth → @nextcloud/axios → @nextcloud/vue),
// so stub it for the unit-test environment.

// Self-referential builder: every chain method (`clearOnLogout`,
// `persist`, etc.) returns the same builder so any call order works
// across @nextcloud/auth versions; `.build()` returns the storage stub.
const builder = {
	clearOnLogout: () => builder,
	persist: () => builder,
	build: () => ({
		getItem: () => null,
		setItem: () => {},
		removeItem: () => {},
		clear: () => {},
		length: 0,
		key: () => null,
	}),
}

module.exports = {
	getBuilder: () => builder,
}
