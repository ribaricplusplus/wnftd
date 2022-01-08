const { activatePlugin } = require( '@wordpress/e2e-test-utils' );

const slug = 'woocommerce-nft-downloads';

beforeAll( async () => {
	await activatePlugin( slug );
} );
