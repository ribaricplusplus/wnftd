<?php

namespace WNFTD\Admin\Meta_Boxes;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;

class Product implements Initializable {

	public function init() {
		\add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_tabs' ) );
		\add_action( 'woocommerce_product_data_panels', array( $this, 'output_panels' ) );
		\add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_meta' ) );
	}

	public function add_tabs( $tabs ) {
		$tabs['nft'] = array(
			'label'    => 'NFT',
			'target'   => 'wnftd_nft_product_data',
			'class'    => array( 'show_if_downloadable' ),
			'priority' => 15,
		);

		return $tabs;
	}

	public function output_panels() {
		\WNFTD\view( 'meta-boxes/product-nft' );
	}

	public function save_meta( $product ) {
		$ids = $_POST['wnftd_product_nfts'] ?? array();
		$ids = array_map( 'absint', $ids );
		foreach ( $ids as $id ) {
			$product->add_meta_data( 'wnftd_product_nft', $id );
		}
	}

}
