<?php

namespace WNFTD\Contracts;

defined( 'ABSPATH' ) || exit;

use Ethereum\DataType\EthQ;

class ERC721 extends \WNFTD\NFT_Contract {

	/**
	 * @var \Ethereum\SmartContract
	 */
	public $smart_contract;

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	public function is_owner( $public_address, $nft ) {
		try {
			$owner = $this->get_owner( $nft );

			if ( empty( $owner ) ) {
				return false;
			}
		} catch ( \Exception $e ) {
			\trigger_error(
				$e->getMessage(),
				\E_USER_NOTICE
			);
			return false;
		}

		return $owner === $public_address;
	}

	/**
	 * @throws \Exception
	 * @return string
	 */
	public function get_owner( $nft ) {
		$token_id = new EthQ( $nft->get_token_id() );
		$owner    = $this->smart_contract->ownerOf( $token_id );

		if ( empty( $owner ) ) {
			return false;
		}

		return $owner->hexVal();
	}

}
