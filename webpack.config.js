const path = require('path')
const fs = require('fs')
const webpackConfig = require('@nextcloud/webpack-vue-config')

const buildMode = process.env.NODE_ENV
const isDev = buildMode === 'development'
webpackConfig.devtool = isDev ? 'cheap-source-map' : 'source-map'

webpackConfig.stats = {
	colors: true,
	modules: false,
}

const appId = 'zaakafhandelapp'
webpackConfig.entry = {
	main: {
		import: path.join(__dirname, 'src', 'main.js'),
		filename: appId + '-main.js',
	},
	adminSettings: {
		import: path.join(__dirname, 'src', 'settings.js'),
		filename: appId + '-settings.js',
	},
	zakenWidget: {
		import: path.join(__dirname, 'src', 'zakenWidget.js'),
		filename: appId + '-zakenWidget.js',
	},
	takenWidget: {
		import: path.join(__dirname, 'src', 'takenWidget.js'),
		filename: appId + '-takenWidget.js',
	},
	openZakenWidget: {
		import: path.join(__dirname, 'src', 'openZakenWidget.js'),
		filename: appId + '-openZakenWidget.js',
	},
	contactmomentenWidget: {
		import: path.join(__dirname, 'src', 'contactmomentenWidget.js'),
		filename: appId + '-contactmomentenWidget.js',
	},
	personenWidget: {
		import: path.join(__dirname, 'src', 'personenWidget.js'),
		filename: appId + '-personenWidget.js',
	},
	organisatiesWidget: {
		import: path.join(__dirname, 'src', 'organisatiesWidget.js'),
		filename: appId + '-organisatiesWidget.js',
	},
}

// Use local source when available (monorepo dev), otherwise fall back to the
// npm package. `USE_LOCAL_LIB=false` forces the published package even when a
// sibling checkout is present.
//
// ⚠️ This alias silently OVERRIDES the exactly-pinned
// `@conduction/nextcloud-vue` dependency, and `USE_LOCAL_LIB` is opt-OUT. The
// shared `apps-extra/nextcloud-vue` checkout is regularly parked on the Vue 2
// (`1.x` / `beta.*`) line, and `apps-extra/zaakafhandelapp` sits right next to
// it — so a build from the shared checkout would compile Vue 2 library sources
// into this Vue 3 app. The build SUCCEEDS; the first symptom is a runtime
// failure that reads as a migration bug. Refuse a MAJOR mismatch loudly.
const localLib = path.resolve(__dirname, '../nextcloud-vue/src')

/**
 * Decide whether the sibling nc-vue checkout may be aliased in.
 *
 * @return {boolean} True when the local source should replace the npm package.
 */
function resolveUseLocalLib() {
	// Opt-IN (ADR-090). This was opt-OUT, and unset — its normal state — meant
	// "alias whatever sibling is on disk into a build that can ship".
	if (process.env.USE_LOCAL_LIB !== 'true' || !fs.existsSync(localLib)) {
		return false
	}

	// The MAJOR comparison this replaces was blind to the exact skew the comment
	// above warns about. nc-vue's Vue 2 line and its Vue 3 line are BOTH major 2
	// — the sibling is 2.0.5 (Vue 2) and this app declares ^2.3.0 (Vue 3) — so
	// the guard compared 2 against 2, passed, and aliased Vue 2 sources in.
	// Compare against the declared RANGE, which is what the skew actually
	// violates.
	let localVersion = 'unreadable'
	let satisfied = false
	try {
		// eslint-disable-next-line n/no-extraneous-require
		const semver = require('semver')
		const required =
			require('./package.json').dependencies['@conduction/nextcloud-vue']
		localVersion = String(
			JSON.parse(
				fs.readFileSync(
					path.resolve(localLib, '..', 'package.json'),
					'utf8',
				),
			).version || '',
		)
		satisfied = semver.satisfies(localVersion, required, {
			includePrerelease: true,
		})
	} catch (e) {
		// Fail CLOSED: if the check cannot run, the sibling is refused.
		satisfied = false
	}

	if (!satisfied) {
		// A warning rather than a throw: refusing the sibling still produces a
		// complete, correct build against the pinned npm package.
		// eslint-disable-next-line no-console
		console.warn(
			`[${appId}] IGNORING sibling @conduction/nextcloud-vue@${localVersion} — `
				+ "it does not satisfy this app's declared range. Building against the npm dist.",
		)
		return false
	}

	return true
}

const useLocalLib = resolveUseLocalLib()

webpackConfig.resolve = webpackConfig.resolve || {}
webpackConfig.resolve.modules = [
	path.resolve(__dirname, 'node_modules'),
	'node_modules',
]
webpackConfig.resolve.alias = {
	...(webpackConfig.resolve.alias || {}),
	'@': path.resolve(__dirname, 'src/'),
	...(useLocalLib ? { '@conduction/nextcloud-vue': localLib } : {}),
	// Deduplicate shared packages so the aliased library source uses the same
	// instances as the app (prevents dual-Pinia / dual-Vue bugs). `vue` and
	// `pinia` still declare `main`, so a DIRECTORY alias resolves for them.
	vue$: path.resolve(__dirname, 'node_modules/vue'),
	pinia$: path.resolve(__dirname, 'node_modules/pinia'),
	// MANDATORY, not an optimisation. `@nextcloud/vue@9` hard-depends on
	// `vue-router ^5.1.0` while this app is on `vue-router@4`, so npm installs
	// BOTH — `node_modules/vue-router` (4.x) and
	// `node_modules/@nextcloud/vue/node_modules/vue-router` (5.x). Without this
	// exact-match alias `main.js` gets the 4.x singleton while every
	// `@nextcloud/vue` component calling `useRoute()` / `useRouter()` resolves
	// the 5.x copy — a DIFFERENT injection key, so those components see no
	// router at all and `<NcAppNavigationItem :to="…">` renders inert with
	// nothing logged.
	'vue-router$': path.resolve(
		__dirname,
		'node_modules/vue-router/dist/vue-router.mjs',
	),
	// These MUST point at the entry FILE, not the package directory.
	// @nextcloud/vue@9 and @nextcloud/dialogs@7 declare no `main` and no
	// `module` — only an `exports` map, which webpack applies to *package
	// requests* and never to an already-absolutised path. A directory alias
	// therefore resolves to nothing and every `from '@nextcloud/vue'` in the
	// app AND inside @conduction/nextcloud-vue's dist fails with
	// "Can't resolve '@nextcloud/vue'".
	//
	// The `$` exact-match suffix matters just as much: without it the alias
	// would also rewrite subpaths such as `@nextcloud/dialogs/style.css`,
	// which must keep going through the exports map.
	'@nextcloud/vue$': path.resolve(
		__dirname,
		'node_modules/@nextcloud/vue/dist/index.mjs',
	),
	'@nextcloud/dialogs$': path.resolve(
		__dirname,
		'node_modules/@nextcloud/dialogs/dist/index.mjs',
	),
	// Bypass @nextcloud/axios's `exports` field which only declares the
	// `import` condition, so the library's transitive CJS `require()` resolves
	// to this app's installed copy and shares interceptors / CSRF tokens.
	'@nextcloud/axios$': path.resolve(
		__dirname,
		'node_modules/@nextcloud/axios/dist/index.cjs',
	),
}

// Allow `.js` import requests to resolve to `.cjs` files. @nextcloud/vue ships
// .cjs/.mjs; without this, `import './foo.js'` inside its ESM dist fails to
// find `./foo.cjs`.
webpackConfig.resolve.extensionAlias = {
	'.js': ['.cjs', '.js'],
	...(webpackConfig.resolve.extensionAlias || {}),
}

// @nextcloud/dialogs drags in a FilePicker chunk that imports node's `path`,
// and webpack 5 no longer auto-polyfills node core modules. Supply the real
// shim rather than an empty module — under @nextcloud/vue@9 the FilePicker is
// reachable from components the library pulls in.
webpackConfig.resolve.fallback = {
	...(webpackConfig.resolve.fallback || {}),
	path: require.resolve('path-browserify'),
}

// `@nextcloud/webpack-vue-config` hardcodes `output.publicPath` to
// `/apps/<appName>/js/`. Apps `docker cp`-deployed under `custom_apps/` are
// served from `/custom_apps/<app>/js/`, and the wrong path does NOT 404 —
// Nextcloud answers 200 with `text/html`, so the browser refuses it on MIME
// grounds and the page dies with a `ChunkLoadError` rather than a missing-file
// error. The entry bundles are unaffected (Nextcloud writes those script tags
// itself), so the build looks clean and only lazy chunks break. Vue 2 barely
// surfaced this because it emitted almost no async chunks; the Vue 3
// dependency set splits @nextcloud/dialogs, @nextcloud/files and @mdi/js into
// dozens. `'auto'` derives the path at runtime from the URL the entry script
// was actually loaded from, so it is correct under every apps path.
webpackConfig.output = {
	...webpackConfig.output,
	publicPath: 'auto',
}

// Drop the base config's ts-loader rule (its module-ID scheme conflicts with
// the base's babel-loader and breaks `chunks: 'all'` splitChunks — ADR-004
// 'Build / bundling — TypeScript apps'). Replace with a babel-loader rule
// using @babel/preset-typescript via .babelrc, so .ts files go through the
// SAME babel-loader as the .js files. One module-ID space, splitChunks
// survives, type-checking moves to `npx tsc --noEmit` (opt-in).
webpackConfig.module.rules = webpackConfig.module.rules.filter(
	(rule) =>
		!(
			rule
			&& rule.use
			&& ((typeof rule.use === 'string' && rule.use === 'ts-loader')
				|| (Array.isArray(rule.use)
					&& rule.use.some((u) => (u?.loader || u) === 'ts-loader'))
				|| (typeof rule.use === 'object' && rule.use.loader === 'ts-loader'))
		) && !(rule && rule.loader === 'ts-loader'),
)
webpackConfig.module.rules.push({
	test: /\.ts$/,
	exclude: /node_modules/,
	use: { loader: 'babel-loader' },
})

// Share Vue + @nextcloud/vue + pinia + icons + @conduction/nextcloud-vue
// across every entry-point so each widget bundle no longer inlines its own
// ~5 MB framework copy. Stable filenames mean each widget's `Util::addScript`
// PHP call can reference the chunk directly without a manifest. See ADR-004
// (Build / bundling) for the org-wide pattern.
webpackConfig.optimization = {
	...(webpackConfig.optimization || {}),
	// Consolidate the runtime into one chunk shared across entries. Without
	// this, each entry has its own runtime + module-ID space, and split
	// chunks register modules into the wrong runtime → cross-chunk require()
	// fails at first widget mount.
	runtimeChunk: { name: 'runtime' },
	splitChunks: {
		...(webpackConfig.optimization?.splitChunks || {}),
		chunks: 'all',
		cacheGroups: {
			default: false,
			defaultVendors: false,
			ncVue: {
				name: appId + '-shared-nc-vue',
				// Matches both node_modules entries AND the monorepo-dev alias
				// `../nextcloud-vue/src/...` which webpack resolves outside
				// node_modules when @conduction/nextcloud-vue is aliased to it.
				test: /[\\/]node_modules[\\/](@nextcloud[\\/]vue|@conduction[\\/]nextcloud-vue)[\\/]|[\\/]nextcloud-vue[\\/]src[\\/]/,
				priority: 30,
				reuseExistingChunk: true,
				enforce: true,
				filename: appId + '-shared-nc-vue.js',
			},
			vendor: {
				// Catch-all for EVERY remaining node_modules dependency (lower
				// priority than ncVue). A hand-maintained allowlist left any
				// transitive library @conduction/nextcloud-vue requires in the
				// main entry chunk while the nc-vue shared chunk
				// __webpack_require__'d its factory — the nc-vue chunk loads
				// BEFORE main, so the factory was undefined → "Cannot read
				// properties of undefined (reading 'call')" at first mount.
				name: appId + '-shared-vendor',
				test: /[\\/]node_modules[\\/]/,
				priority: 20,
				reuseExistingChunk: true,
				enforce: true,
				filename: appId + '-shared-vendor.js',
			},
		},
	},
}

module.exports = webpackConfig
