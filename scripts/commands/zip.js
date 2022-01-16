const glob = require ( 'glob-all' )
const AdmZip = require ( 'adm-zip' )
const path = require('path')
const { execSync } = require('child_process')

async function main() {

	const ROOT_DIR = path.join( __dirname, '../..' )

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
		'!**/*.map'
	]

	const files = glob.sync(patterns)

	const zip = new AdmZip()
	foreach( const file of files ) {
		zip.addLocalFile( path.join( ROOT_DIR, file ) )
	}

	zip.writeZip( path.join( ROOT_DIR, 'woocommerce-nft-downloads.zip' ) )
}

main()

function buildJavaScript() {
	execSync( 'NODE_ENV=production npx webpack', { stdio: 'inherit' } )
}
