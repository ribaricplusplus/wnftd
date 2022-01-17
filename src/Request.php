<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Request {

	public function post( $url, $args ) {
		return \wp_remote_post( $url, $args );
	}

}
