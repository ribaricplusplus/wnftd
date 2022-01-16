const glob = require ( 'glob-all' )
const path = require('path')
const { execSync } = require('child_process')
const fs = require('fs')

const ROOT_DIR = path.join( __dirname, '../..' )

async function main() {

	process.chdir( ROOT_DIR )

	buildJavaScript()

	const patterns = [
		'*.php',
		'vendor/**/*',
		'src/**/*',
		'build/**/*',
		'contracts/**/*',
		'views/**/*',
		'!secrets.php',
		'!**/*.map',
	]

	const files = glob.sync(patterns).filter( (file) => ! isDirectory( file ) ).join( '\n' )

	execSync( 'zip -@ woocommerce-nft-downloads', { input: files } )
}

function buildJavaScript() {
	execSync( 'NODE_ENV=production npx webpack', { stdio: 'inherit' } )
}

function isDirectory( file ) {
	const filePath = path.join( ROOT_DIR, file )
	return fs.lstatSync(filePath).isDirectory()
}

module.exports = main
