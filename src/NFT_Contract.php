<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

abstract class NFT_Contract {

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	abstract public function is_owner( $public_address, $nft );

}
