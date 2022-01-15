import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';

import App from './App.js';

domReady( () => {
	const root = document.querySelector( '.wnftd-single-product-root' );

	if ( ! root ) {
		throw new Error( 'Failed to render.' );
	}

	render( <App />, root );
} );
