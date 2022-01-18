<?php

namespace WNFTD\Admin;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;

class Settings implements Initializable {

	public function init() {
		add_filter( 'woocommerce_downloadable_products_settings', array( $this, 'add_nft_settings' ) );
	}

	public function add_nft_settings( $settings ) {
		$settings[] = array(
			'title' => __( 'NFT Downloads', 'wnftd' ),
			'type'  => 'title',
			'id'    => 'wnftd_downloads',
		);

		$settings[] = array(
			'title'    => __( 'Ethereum API URL', 'wnftd' ),
			'desc'     => __( 'This URL will be used to read Ethereum blockchain information, e.g. verify if public address owns NFT.' ),
			'type'     => 'text',
			'desc_tip' => true,
			'id'       => 'wnftd_rpc_api_key',
		);

		$settings[] = array(
			'title'    => __( 'Polygon API URL', 'wnftd' ),
			'desc'     => __( 'This URL will be used to read Polygon blockchain information.' ),
			'type'     => 'text',
			'desc_tip' => true,
			'id'       => 'wnftd_rpc_api_key_polygon',
		);

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'wnftd_downloads',
		);
		return $settings;
	}

}
