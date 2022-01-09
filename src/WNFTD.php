<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin file.
 */
class WNFTD {

	public $auth;

	public function __construct() {
		$this->auth = new Authentication();
		$this->auth->init();
		( new Scripts_Loader() )->init();
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
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
