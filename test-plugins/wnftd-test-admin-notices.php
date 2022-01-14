<?php
/**
 * Plugin Name: WNFTD Test Admin Notices
 * Author: Ribarich
 */

namespace WNFTD\Test\Plugins;

\add_action(
	'plugins_loaded',
	function() {
		if ( ! function_exists( '\\WNFTD\\admin' ) ) {
			return;
		}

		\WNFTD\admin()->notices->add_notice( 'test_notice', 'This is a test notice.' );
	}
);
