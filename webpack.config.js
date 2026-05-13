const path = require('path')
const fs = require('fs')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const NodePolyfillPlugin = require('node-polyfill-webpack-plugin')

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

// Use local source when available (monorepo dev), otherwise fall back to npm
const localLib = path.resolve(__dirname, '../nextcloud-vue/src')
const useLocalLib = fs.existsSync(localLib)

webpackConfig.resolve = webpackConfig.resolve || {}
webpackConfig.resolve.modules = [path.resolve(__dirname, 'node_modules'), 'node_modules']
webpackConfig.resolve.alias = {
	...(webpackConfig.resolve.alias || {}),
	'@': path.resolve(__dirname, 'src/'),
	...(useLocalLib ? { '@conduction/nextcloud-vue': localLib } : {}),
	// Deduplicate shared packages so the aliased library source uses the same
	// instances as the app (prevents dual-Pinia / dual-Vue bugs).
	vue$: path.resolve(__dirname, 'node_modules/vue'),
	pinia$: path.resolve(__dirname, 'node_modules/pinia'),
	'@nextcloud/vue$': path.resolve(__dirname, 'node_modules/@nextcloud/vue'),
	'@nextcloud/dialogs': path.resolve(__dirname, 'node_modules/@nextcloud/dialogs'),
	// Bypass @nextcloud/axios's `exports` field which only declares the `import`
	// condition. @nextcloud/vue's CJS bundle still uses require('@nextcloud/axios')
	// and webpack 5's CommonJS resolver fails the exports check with:
	//   "." is not exported under the conditions ["require","module","webpack",...]
	// Aliasing the bare specifier directly at the dist entry sidesteps the
	// exports field gate. Use the $-suffixed exact-match form so subpath imports
	// (e.g. @nextcloud/axios/dist/foo) keep their normal resolution.
	'@nextcloud/axios$': path.resolve(__dirname, 'node_modules/@nextcloud/axios/dist/index.cjs'),
}

webpackConfig.plugins = [
	...(webpackConfig.plugins || []),
	new NodePolyfillPlugin({ additionalAliases: ['process'] }),
]

// Drop the base config's ts-loader rule (its module-ID scheme conflicts with
// the base's babel-loader and breaks `chunks: 'all'` splitChunks — ADR-004
// 'Build / bundling — TypeScript apps'). Replace with a babel-loader rule
// using @babel/preset-typescript via .babelrc, so .ts files go through the
// SAME babel-loader as the .js files. One module-ID space, splitChunks
// survives, type-checking moves to `npx tsc --noEmit` (opt-in).
webpackConfig.module.rules = webpackConfig.module.rules.filter(rule =>
	!(rule && rule.use && (
		(typeof rule.use === 'string' && rule.use === 'ts-loader')
		|| (Array.isArray(rule.use) && rule.use.some(u => (u?.loader || u) === 'ts-loader'))
		|| (typeof rule.use === 'object' && rule.use.loader === 'ts-loader')
	))
	&& !(rule && rule.loader === 'ts-loader')
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
	// fails at first widget mount. opencatalogi gets away without it because
	// its module graph is smaller; zaakafhandelapp's broader graph triggers
	// the cross-runtime resolution path.
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
				name: appId + '-shared-vendor',
				test: /[\\/]node_modules[\\/](vue|pinia|vue-material-design-icons|@vueuse|core-js)[\\/]/,
				priority: 20,
				reuseExistingChunk: true,
				enforce: true,
				filename: appId + '-shared-vendor.js',
			},
		},
	},
}

module.exports = webpackConfig
