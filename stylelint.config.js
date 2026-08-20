// `@nextcloud/stylelint-config`, as the other 17 core apps use — this app was the
// only one extending `stylelint-config-recommended-vue` directly, while carrying
// @nextcloud/stylelint-config in devDependencies and never using it.
//
// That was not a stylistic preference, it was a hole. `stylelint-config-recommended-vue`
// supplies its rules through `.vue` OVERRIDES and declares no top-level `rules`, so:
//
//   src/**/*.vue        linted (the overrides matched)
//   any plain .css/.scss  stylelint aborts with
//                         "No rules found within configuration"
//
// The old glob only reached `src/`, which holds .vue files, so the abort never
// surfaced and the job reported success. Adding `css/` to the glob is what exposed
// it. The fleet-wide `indentation: 'tab'` rule had never applied here at all.
//
// The shared config extends stylelint-config-recommended-scss +
// stylelint-config-recommended-vue/scss and carries the real rule set, so it covers
// .vue as well as plain stylesheets.
module.exports = {
	extends: '@nextcloud/stylelint-config',
}
