const glob = require ( 'glob-all' )
const AdmZip = require ( 'adm-zip' )
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

	const files = glob.sync(patterns).filter( (file) => ! isDirectory( file ) )

	const zip = new AdmZip()
	for( const file of files ) {
		zip.addLocalFile( file )
	}

	zip.writeZip( path.join( ROOT_DIR, 'woocommerce-nft-downloads.zip' ) )
}

function buildJavaScript() {
	execSync( 'NODE_ENV=production npx webpack', { stdio: 'inherit' } )
}

function isDirectory( file ) {
	const filePath = path.join( ROOT_DIR, file )
	return fs.lstatSync(filePath).isDirectory()
}

module.exports = main
