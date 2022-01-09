<?php

namespace WNFTD\Test\REST;

/**
 * Some of this was copied from WordPress test cases.
 */
trait Utils {
	/**
	 * Get a REST request object for given parameters.
	 *
	 * Example endpoint '/wp/v2/search'
	 */
	public function get_request( $endpoint, $params = array(), $method = 'GET' ) {
		$request = new \WP_REST_Request( $method, $endpoint );

		foreach ( $params as $param => $value ) {
			$request->set_param( $param, $value );
		}

		return $request;
	}
}
