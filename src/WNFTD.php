<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use Ethereum\Ethereum;

/**
 * Main plugin file.
 */
class WNFTD {

	public $auth;

	public $ethereum;

	public function __construct() {
		$rpc_api_key = \get_option( 'wnftd_rpc_api_key' );
		if ( $rpc_api_key ) {
			$this->ethereum = new Ethereum( $rpc_api_key );
		} else {
			$this->ethereum = new Ethereum();
		}
		$this->auth = new Authentication( $this->ethereum );
		$this->auth->init();
		( new Scripts_Loader() )->init();

		\add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		\add_action( 'init', array( $this, 'register_post_types_and_taxonomies' ) );
	}

	public function register_post_types_and_taxonomies() {
		\register_taxonomy(
			'wnftd_public_address',
			'user',
			array(
				'public'       => false,
				'hierarchical' => false,
				'show_in_rest' => true,
			)
		);
	}

	public function rest_api_init() {
		$controllers = array(
			'Authentication',
		);
		foreach ( $controllers as $controller ) {
			$class    = __NAMESPACE__ . '\\REST\\' . $controller;
			$instance = new $class();
			$instance->register_routes();
		}
	}

}
