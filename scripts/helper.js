#!/usr/bin/env node
const { program } = require( 'commander' );

program
	.version( '0.0.1' )
	.command( 'get-install-path' )
	.description( 'Get the location of wp-env files' )
	.action( () => {
		const { printInstallPath } = require( './commands/get-install-path' );
		printInstallPath();
	} )

program
	.command( 'zip' )
	.description( 'Build and zip' )
	.action( () => {
		const command = require ( './commands/zip' )
		zip()
	} )

program.parse();
