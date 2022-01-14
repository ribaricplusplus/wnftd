<?php

namespace WNFTD\Admin;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;

class Meta_Boxes implements Initializable {

	public $nft;

	public $product;

	public function init() {
		$this->nft->init();
		$this->product->init();

		\add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function __construct( $nft, $product ) {
		$this->nft     = $nft;
		$this->product = $product;
	}

	public function add_meta_boxes() {
		\add_meta_box( 'wnftd-nft-data', __( 'NFT data', 'wnftd' ), array( $this->nft, 'output' ), 'wnftd-nft', );
	}
}
