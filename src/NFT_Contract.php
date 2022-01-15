<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

abstract class NFT_Contract {

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	public function is_owner( $public_address, $nft ) {
		return \WNFTD\public_addresses_equal( $public_address, $nft->get_fake_owner() );
	}

	public function should_use_fake_owner( $nft ) {
		return ! empty( $nft->get_fake_owner() );
	}

}
