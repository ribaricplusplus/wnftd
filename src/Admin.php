<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Admin implements Interfaces\Initializable {

	/** @var Admin\Notices */
	public $notices;

	public function init() {
		$this->notices->init();
	}

	public function __construct( $notices ) {
		$this->notices = $notices;
	}

	public function add_notice( $name, $message ) {
		return $this->notices->add_notice( $name, $message );
	}

}
