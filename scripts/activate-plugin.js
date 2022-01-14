const {
	activatePlugin,
	activateTheme,
} = require( '@wordpress/e2e-test-utils' );

const slug = 'woocommerce-nft-downloads';

beforeAll( async () => {
	await activatePlugin( 'woocommerce' );
	await activateTheme( 'jot-shop' );
	await activatePlugin( slug );
} );
