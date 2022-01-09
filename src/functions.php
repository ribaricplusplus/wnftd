<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

function instance() {
	static $instance = null;

	if ( ! $instance ) {
		$instance = new WNFTD();
	}

	return $instance;
}

/**
 * @param string $nonce
 * @return string
 */
function get_auth_message( $nonce ) {
	return sprintf(
		__( 'Sign this message to authenticate with the application. Here is a nonce for security: %s' ),
		$nonce
	);
}

function auth() {
	return instance()->auth;
}
