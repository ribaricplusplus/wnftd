<?php

namespace WNFTD\Test\Utils;

trait Request {
	/**
	 * @param array $args
	 * @return array
	 */
	public function create_response( $args ) {
		$args = \wp_parse_args(
			$args,
			array(
				'body'        => '',
				'success'     => true,
				'status_code' => 200,
			)
		);

		$rr              = new \Requests_Response();
		$rr->body        = $args['body'];
		$rr->success     = $args['success'];
		$rr->status_code = $args['status_code'];
		$response        = new \WP_HTTP_Requests_Response( $rr );
		return $response->to_array();
	}
}
