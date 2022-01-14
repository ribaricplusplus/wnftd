<?php

namespace WNFTD\Admin;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces;

class Notices implements Interfaces\Initializable {

	private $initialized;

	public $notices = array();

	public function init() {
		if ( $this->initialized ) {
			return;
		}

		\add_action( 'admin_notices', array( $this, 'output_notices' ) );

		$this->initialized = true;
	}

	public function add_notice( $name, $message ) {
		$this->notices[ $name ] = array(
			'message' => $message,
		);
	}

	public function output_notices() {
		foreach ( $this->notices as $name => $notice ) {
			\WNFTD\view( 'notices/error', $notice );
		}
	}

}
