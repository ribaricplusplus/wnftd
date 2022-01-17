import {
	activatePlugin,
	deactivatePlugin,
	visitAdminPage
} from '@wordpress/e2e-test-utils';

describe( 'GMP extension initialization checks', () => {
	beforeAll( async () => {
		await activatePlugin( 'wnftd-test-disable-gmp-extension' );
	} )

	afterAll( async () => {
		await deactivatePlugin( 'wnftd-test-disable-gmp-extension' );
	} );

	it( 'Shows up admin error when GMP extension is unavailable', async () => {
		await visitAdminPage( '/' )
		await expect(page).toMatchElement( '.wnftd-notice', { text: /Missing GMP extension/i } )
	} )
} )
