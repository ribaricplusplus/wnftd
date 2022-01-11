<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

abstract class NFT_Contract {

	/**
	 * @var \Ethereum\SmartContract
	 */
	public $smart_contract;

	/**
	 * @param NFT $nft
	 * @return string Owner public address.
	 */
	abstract public function owner_of( $nft );

}
