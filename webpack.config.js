const path = require('path')
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

webpackConfig.resolve = webpackConfig.resolve || {}
webpackConfig.resolve.modules = [path.resolve(__dirname, 'node_modules'), 'node_modules']
webpackConfig.resolve.alias = {
	...(webpackConfig.resolve.alias || {}),
	'@': path.resolve(__dirname, 'src/'),
}

webpackConfig.plugins = [
	...(webpackConfig.plugins || []),
	new NodePolyfillPlugin({ additionalAliases: ['process'] }),
]

module.exports = webpackConfig
