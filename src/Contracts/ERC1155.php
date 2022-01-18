<?php

namespace WNFTD\Contracts;

defined( 'ABSPATH' ) || exit;

use kornrunner\Keccak;

class ERC1155 extends \WNFTD\NFT_Contract {

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	public function is_owner( $public_address, $nft ) {
		if ( $this->should_use_fake_owner( $nft ) ) {
			return parent::is_owner( $public_address, $nft );
		}
		try {
			$balance = $this->get_balance( $public_address, $nft );
			return $balance > 0;
		} catch ( \Exception $e ) {
			trigger_error(
				$e->getMessage(),
				\E_USER_NOTICE
			);
			return false;
		}
	}

	/**
	 * @param string     $public_address
	 * @param \WNFTD\NFT $nft
	 * @throws \Exception
	 * @return int
	 */
	public function get_balance( $public_address, $nft ) {
		$json     = $this->get_balance_of_json_rpc( $public_address, $nft->get_token_id() );
		$json     = wp_json_encode( $json );
		$response = \wp_remote_post( $this->api_url, array( 'body' => $json ) );

		return $this->get_balance_from_response( $response );
	}

	/**
	 * The Ethereum library seems to give incorrectly encoded data... Easier to
	 * write a function from scratch than to debug what's going on there.
	 *
	 * @throws \Exception
	 */
	public function get_balance_of_json_rpc( $public_address, $token_id ) {
		if ( ! is_numeric( $token_id ) || ! is_string( 'public_address' ) ) {
			throw new \InvalidArgumentException();
		}

		$json               = $this->get_json_rpc_base();
		$function_signature = 'balanceOf(address,uint256)';
		$hash               = Keccak::Hash( $function_signature, 256 );
		$data               = '0x' . substr( $hash, 0, 8 );
		$data              .= $this->encode_type( 'address', $public_address );
		$data              .= $this->encode_type( 'uint256', $token_id );

		$json['params'][0]['data'] = $data;
		return $json;
	}

}
