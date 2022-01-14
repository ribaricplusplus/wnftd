<?php

namespace WNFTD\Contracts;

defined( 'ABSPATH' ) || exit;

use kornrunner\Keccak;

class ERC1155 extends \WNFTD\NFT_Contract {
	public $contract_address;

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
		$response = \wp_remote_post( \WNFTD\get_api_key(), array( 'body' => $json ) );

		return $this->get_balance_from_response( $response );
	}

	/**
	 * The Ethereum library seems to give incorrectly encoded data... Easier to
	 * write a function from scratch than to debug what's going on there.
	 */
	public function get_balance_of_json_rpc( $public_address, $token_id ) {
		if ( ! is_numeric( $token_id ) || ! is_string( 'public_address' ) ) {
			throw new \InvalidArgumentException();
		}

		$json               = array(
			'id'      => \wp_generate_uuid4(),
			'jsonrpc' => '2.0',
			'method'  => 'eth_call',
			'params'  => array(
				array(
					'to' => $this->contract_address,
				),
			),
		);
		$function_signature = 'balanceOf(address,uint256)';
		$hash               = Keccak::Hash( $function_signature, 256 );
		$data               = '0x' . substr( $hash, 0, 8 );
		$data              .= str_pad( ltrim( $public_address, '0x' ), 32 * 2, '0', \STR_PAD_LEFT );
		$data              .= str_pad( (string) dechex( $token_id ), 32 * 2, '0', \STR_PAD_LEFT );

		$json['params'][0]['data'] = $data;
		return $json;
	}

	/**
	 * @param array $response
	 * @throws \InvalidArgumentException
	 * @return int
	 */
	public function get_balance_from_response( $response ) {
		if ( \is_wp_error( $response ) ) {
			throw new \InvalidArgumentException();
		}

		if ( ! \is_array( $response ) ) {
			throw new \InvalidArgumentException();
		}

		if ( ! absint( $response['code'] ) === 200 ) {
			throw new \InvalidArgumentException();
		}

		$json = \json_decode( $response['body'], true, \JSON_BIGINT_AS_STRING );

		if ( empty( $json ) || empty( $json['result'] ) ) {
			throw new \InvalidArgumentException();
		}

		return hexdec( $hexbalance );
	}

}
