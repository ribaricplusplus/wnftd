<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

abstract class NFT_Contract {

	/** @var string */
	public $contract_address;

	/** @var string */
	public $network;

	/** @var string */
	public $api_url;

	public function __construct( $contract_address = '', $network = 'polygon' ) {
		$this->contract_address = $contract_address;
		$this->network          = $network;
		$this->api_url          = \WNFTD\get_api_key( $network );
	}

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	public function is_owner( $public_address, $nft ) {
		return \WNFTD\public_addresses_equal( $public_address, $nft->get_fake_owner() );
	}

	public function should_use_fake_owner( $nft = null ) {
		if ( empty( $nft ) ) {
			return false;
		}

		return ! empty( $nft->get_fake_owner() );
	}

	public function get_json_rpc_base() {
		return array(
			'id'      => \wp_generate_uuid4(),
			'jsonrpc' => '2.0',
			'method'  => 'eth_call',
			'params'  => array(
				array(
					'to' => $this->contract_address,
				),
			),
		);
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

		if ( empty( $response['response']['code'] ) || ! absint( $response['response']['code'] ) === 200 ) {
			throw new \InvalidArgumentException();
		}

		$json = \json_decode( $response['body'], true, \JSON_BIGINT_AS_STRING );

		if ( empty( $json ) || empty( $json['result'] ) ) {
			throw new \InvalidArgumentException();
		}

		$hexbalance = $json['result'];

		return hexdec( $hexbalance );
	}


	/**
	 * @throws \Exception
	 */
	public function encode_type( $type, $value ) {
		switch ( $type ) {
			case 'address':
			case 'uint256':
				return $this->{"encode_type_$type"}( $value );
		}

		throw new \InvalidArgumentException( 'Unknown type.' );
	}

	function encode_type_address( $value ) {
		return str_pad( ltrim( $value, '0x' ), 32 * 2, '0', \STR_PAD_LEFT );
	}

	function encode_type_uint256( $value ) {
		return str_pad( (string) dechex( $value ), 32 * 2, '0', \STR_PAD_LEFT );
	}

}
