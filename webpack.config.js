const path = require('path')
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

webpackConfig.resolve = {
	extensions: ['.ts', '.js', '.vue', '.json'],
	alias: {
		'@': path.resolve(__dirname, 'src/'),
	},
}

module.exports = webpackConfig
