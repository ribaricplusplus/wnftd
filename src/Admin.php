<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Admin implements Interfaces\Initializable {

	/** @var Admin\Notices */
	public $notices;

	/** @var Admin\Meta_Boxes */
	public $meta_boxes;

	public function init() {
		$this->notices->init();
		$this->meta_boxes->init();
		$this->scripts->init();

		\add_filter( 'woocommerce_screen_ids', array( $this, 'add_woocommerce_screen_ids' ) );
	}

	public function add_woocommerce_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-wnftd-nft';
		$screen_ids[] = 'wnftd-nft';
		return $screen_ids;
	}

	public function __construct( $notices, $meta_boxes, $scripts ) {
		$this->notices    = $notices;
		$this->meta_boxes = $meta_boxes;
		$this->scripts    = $scripts;
	}

	public function add_notice( $name, $message ) {
		return $this->notices->add_notice( $name, $message );
	}

}
