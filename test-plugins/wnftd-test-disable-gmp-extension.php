<?php
/**
 * Plugin Name: WNFTD Test Disable GMP Extension
 * Author: Ribarich
 */

namespace WNFTD\Test\Plugins;

\add_filter(
	'wnftd_is_extension_loaded',
	function( $is_loaded, $extension ) {

		if ( $extension === 'gmp' ) {
			return false;
		}

		return $is_loaded;

	},
	10,
	2
);
