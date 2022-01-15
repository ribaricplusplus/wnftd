<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;

class Template_Controller implements Initializable {

	public $product_controller;

	public function init() {
		add_filter( 'wc_get_template', array( $this, 'override_simple_product_templates' ), 1000, 2 );
	}

	public function __construct( $product_controller ) {
		$this->product_controller = $product_controller;
	}

	public function override_simple_product_templates( $template, $template_name ) {
		global $post;

		if ( empty( $post ) || $post->post_type !== 'product' ) {
			return $template;
		}

		if ( ! $this->product_controller->is_nft_restricted( $post->ID ) ) {
			return $template;
		}

		switch ( $template_name ) {
			case 'single-product/add-to-cart/simple.php':
				return \plugin_dir_path( \WNFTD_FILE ) . 'views/add-to-cart.php';
			case 'single-product/price.php':
				return \plugin_dir_path( \WNFTD_FILE ) . 'views/empty.php';
		}

		return $template;
	}

}
