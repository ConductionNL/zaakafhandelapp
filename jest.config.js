module.exports = {
	transform: {
		'^.+\\.vue$': '@vue/vue2-jest',
		'^.+\\.[cm]?js$': 'babel-jest',
		'^.+\\.ts$': 'ts-jest',
		'.+\\.(css|styl|less|sass|scss|png|jpg|ttf|woff|woff2)$': 'jest-transform-stub',
	},
	moduleFileExtensions: ['js', 'json', 'vue', 'ts'],
	testEnvironment: 'jest-environment-jsdom',
	// Several @nextcloud/* and @conduction/* packages ship pure ESM (or
	// CJS chunks that `require('....css')`), which Jest cannot parse out
	// of the box. Run them through babel-jest so the test suite can
	// import store modules that transitively pull NC/Conduction vue
	// components.
	transformIgnorePatterns: [
		'/node_modules/(?!(@nextcloud|@conduction|tributejs|escape-html|webdav|p-cancelable|p-limit|p-queue|p-timeout|node-fetch|fetch-blob|formdata-polyfill|data-uri-to-buffer|filesize|nanoid|pinia|toastify-js|vue-material-design-icons|axios|zod|cancelable-promise)/)',
	],
	moduleNameMapper: {
		'^@/(.*)$': '<rootDir>/src/$1',
		// @nextcloud/browser-storage ships pure ESM with `"type": "module"`.
		// Babel-jest can't transparently transform it from a CJS dependency
		// chain, so stub it for the unit-test environment.
		'^@nextcloud/browser-storage$': '<rootDir>/tests/__mocks__/nextcloud-browser-storage.js',
		// Stub the entire @conduction/nextcloud-vue package — its real
		// require chain pulls in @nextcloud/vue (~MB of CSS+CJS+ESM, plus
		// JSDOM-incompatible globals like structuredClone) that jest
		// cannot transform. The store unit tests only need the factory
		// exports (createObjectStore, useObjectStore, *Plugin); the rest
		// is reached transitively through router/index.js → store/*.js.
		'^@conduction/nextcloud-vue$': '<rootDir>/tests/__mocks__/conduction-nextcloud-vue.js',
		// Stub CSS / asset imports inside node_modules. Default
		// `transformIgnorePatterns` excludes node_modules from the
		// `jest-transform-stub` rule, so a require('....css') from
		// @nextcloud/vue otherwise crashes the parser.
		'\\.(css|less|sass|scss|png|jpg|ttf|woff|woff2)$': 'jest-transform-stub',
	},
	coveragePathIgnorePatterns: [
		'index.js',
		'index.ts',
	],
	coverageDirectory: '<rootDir>/coverage-frontend/',
}
