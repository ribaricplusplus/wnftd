<?php

namespace WNFTD\REST;

defined( 'ABSPATH' ) || exit;

use WNFTD\Factory;

class Nonces extends \WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wnftd/v1';
		$this->rest_base = 'nonces';
	}

	public function register_routes() {
		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_nonces' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_args( 'nonces' ),
				),
			)
		);
	}

	/**
	 * Response format: Array<{ [action: string]: string }>
	 */
	public function get_nonces( $request ) {
		try {
			$actions = $request['actions'];
			$data    = array();

			foreach ( $actions as $action ) {
				$data[ $action ] = \wp_create_nonce( $action );
			}

			return \rest_ensure_response( $data );
		} catch ( \Exception $e ) {
			return Factory::create_rest_error( 'server_error' );
		}
	}

	/**
	 * @throws \Exception
	 */
	public function get_args( $endpoint ) {
		switch ( $endpoint ) {
			case 'nonces':
				return array(
					'actions' => array(
						'type'  => 'array',
						'items' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_key',
						),
					),
				);
		}

		throw new \Exception();
	}
}
