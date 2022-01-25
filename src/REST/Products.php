<?php

namespace WNFTD\REST;

defined( 'ABSPATH' ) || exit;

use WNFTD\Factory;

class Products extends \WP_REST_Controller {
	public $product_controller;

	public function __construct( $product_controller ) {
		$this->product_controller = $product_controller;
		$this->namespace          = 'wnftd/v1';
		$this->rest_base          = 'products';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/download',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_download_link' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_args( 'download' ),
				),
			)
		);
	}

	public function permission_callback( $request ) {
		try {
			if ( ! \is_user_logged_in() ) {
				return new \WP_Error(
					'wnftd_forbidden',
					'Permission denied',
					array( 'status' => 401 )
				);
			}

			if ( empty( $request['wnftd_product_nonce'] ) || ! \wp_verify_nonce( $request['wnftd_product_nonce'], 'wnftd_product_download' ) ) {
				return Factory::create_rest_error( 'invalid_nonce' );
			}

			$user    = \wp_get_current_user();
			$product = new \WC_Product( $request['id'] );

			if ( ! $this->product_controller->grant_access_by_nft( $user->ID, $product ) ) {
				return new \WP_Error(
					'wnftd_nft_verification_failed',
					'NFT ownership not verified',
					array( 'status' => 401 )
				);
			}

			return true;
		} catch ( \Exception $e ) {
			\trigger_error(
				$e->getMessage(),
				\E_USER_NOTICE
			);
			return new \WP_Error(
				'wnftd_permission_exception',
				'An exception occurred in permission verification.',
				array( 'status' => 500 )
			);
		}
	}

	public function get_download_link( $request ) {
		try {
			$has_permission = $this->permission_callback( $request );

			if ( is_wp_error( $has_permission ) ) {
				return $has_permission;
			}

			if ( empty( $has_permission ) ) {
				return new \WP_Error(
					'wnftd_forbidden',
					'Permission denied',
					array( 'status' => 401 )
				);
			}

			$id                  = $request['id'];
			$downloads           = wc_get_customer_available_downloads( \get_current_user_id() );
			$requested_downloads = \wp_filter_object_list(
				$downloads,
				array(
					'product_id' => $id,
				),
			);

			$data = \WNFTD\filter_keys( $requested_downloads, array( 'download_url', 'download_name' ) );

			return \rest_ensure_response(
				array(
					'code' => 'access_granted',
					'data' => $data,
				)
			);
		} catch ( \Exception $e ) {
			return Factory::create_rest_error();
		}
	}

	public function get_args( $endpoint ) {
		switch ( $endpoint ) {
			case 'download':
				return array(
					'id'                  => array(
						'type'              => 'number',
						'sanitize_callback' => 'absint',
						'required'          => true,
					),
					'wnftd_product_nonce' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
						'required'          => true,
					),
				);
		}

		return array();
	}

}
