import {
	activatePlugin,
	deactivatePlugin,
	visitAdminPage
} from '@wordpress/e2e-test-utils';

describe( 'Admin notices', () => {
	beforeAll( async () => {
		await activatePlugin( 'wnftd-test-admin-notices' );
	} );

	afterAll( async () => {
		await deactivatePlugin( 'wnftd-test-admin-notices' );
	} );

	it( 'Shows up', async () => {
		await visitAdminPage( '/' );
		await expect(page).toMatchElement( '.wnftd-notice', { text: /This is a test notice/i } )
	} );

} )
