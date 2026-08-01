const {
	defineConfig,
} = require('@eslint/config-helpers')

const js = require('@eslint/js')

const {
	FlatCompat,
} = require('@eslint/eslintrc')

// Shared Vue 3 correction layer from @conduction/nextcloud-vue.
//
// `@nextcloud/eslint-config@8` (pulled in via FlatCompat below) resolves
// eslint-plugin-vue's **Vue 2** preset. That is not merely stale: two of its
// rules are INVERTED under Vue 3, and NONE of the `vue/no-deprecated-*` rules
// are active. Verified on this repo before the migration: `eslint --print-config
// src/App.vue` listed zero `vue/no-deprecated-*` rules at a non-`off` severity,
// while `vue/no-v-model-argument` and `vue/no-v-for-template-key` were both at
// severity 2. Vue 2 idioms therefore survive a migration silently, and
// `beforeDestroy` is the dangerous case because Vue 3 never calls that hook.
//
// `conductionVue3Fixes` is an ARRAY of three flat-config objects (language
// level, SFC parser, deprecation rules). It deliberately registers no plugins,
// so it layers cleanly onto the `@nextcloud` base — and must be spread **last**
// to win over the preset it is correcting.
//
// It enables `vue/v-on-event-hyphenation` with `ignore: ['update:modelValue']`.
// That exception is load-bearing — Nextcloud Vue 3 field components read
// `onUpdate:modelValue` directly via `useModel`, so the hyphenated
// `@update:model-value` form is silently dead.
const {
	conductionVue3Fixes,
} = require('@conduction/nextcloud-vue/eslint')

const compat = new FlatCompat({
	baseDirectory: __dirname,
	recommendedConfig: js.configs.recommended,
	allConfig: js.configs.all,
})

module.exports = defineConfig([{
	extends: compat.extends('@nextcloud'),

	settings: {
		'import/resolver': {
			alias: {
				map: [
					['@', './src'],
				],
				extensions: ['.js', '.ts', '.vue', '.json', '.css'],
			},
		},
	},

	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
		'@typescript-eslint/no-explicit-any': 'off',
		// `@spec` is the fleet-wide OpenSpec traceability tag (hydra gate-16);
		// whitelist it so tagged methods don't warn (same as pipelinq/procest).
		'jsdoc/check-tag-names': ['warn', { definedTags: ['spec'] }],
		// The alias resolver above cannot follow packages that ship only an
		// `exports` map (@nextcloud/vue@9, @nextcloud/dialogs@7, vue-router@4),
		// so these produce false positives rather than real findings.
		'import/namespace': 'off',
		'import/default': 'off',
		'import/no-named-as-default': 'off',
		'import/no-named-as-default-member': 'off',
		'n/no-missing-import': 'off',
	},
}, {
	// The flat config enrols `.ts` into the lint set (the eslintrc setup this
	// replaced only ever ran over `.js`/`.vue`), which newly exposes the store
	// modules to `eslint-plugin-jsdoc`. That plugin does not read TypeScript
	// type declarations, so every `@param {TBesluit}` / `@return {Besluit}`
	// referring to a real, imported TS type is reported as "undefined" — 40
	// warnings, all false. TypeScript already checks these (`npx tsc --noEmit`),
	// so the rule is redundant here rather than merely inconvenient.
	files: ['**/*.ts'],
	rules: {
		'jsdoc/no-undefined-types': 'off',
	},
}, ...conductionVue3Fixes])
