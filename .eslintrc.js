module.exports = {
	extends: [
		'@nextcloud',
	],
	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
		'@typescript-eslint/no-explicit-any': 'off',
		// `@spec` is the fleet-wide OpenSpec traceability tag (hydra gate-16);
		// whitelist it so tagged methods don't warn (same as pipelinq/procest).
		'jsdoc/check-tag-names': ['warn', { definedTags: ['spec'] }],
	},
}
