<?php

namespace WNFTD\Contracts;

defined( 'ABSPATH' ) || exit;

use Ethereum\DataType\EthQ;
use kornrunner\Keccak;

class ERC721 extends \WNFTD\NFT_Contract {

	/**
	 * @var \Ethereum\SmartContract
	 */
	public $smart_contract;

	/**
	 * @var \WNFTD\Request
	 */
	public $request;

	public $contract_address;

	public function __construct( $smart_contract = null, $request = null, $contract_address = '', $network = 'polygon' ) {
		parent::__construct( $contract_address, $network );
		$this->smart_contract = $smart_contract;
		$this->request        = $request;
	}

	/**
	 * @param string $public_address
	 * @param NFT    $nft
	 * @return bool
	 */
	public function is_owner( $public_address, $nft = null ) {
		try {
			if ( $this->should_use_fake_owner( $nft ) ) {
				return parent::is_owner( $public_address, $nft );
			}

			if ( empty( $nft ) || empty( $nft->get_token_id() ) ) {
				return $this->is_owner_of_any_nft( $public_address );
			}

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

		return \WNFTD\public_addresses_equal( $owner, $public_address );
	}

	/**
	 * @throws \Exception
	 */
	public function is_owner_of_any_nft( $public_address ) {
		$balance = $this->get_balance_for_public_address( $public_address );
		return $balance > 0;
	}

	/**
	 * @throws \Exception
	 */
	public function get_balance_for_public_address( $public_address ) {
		$json     = $this->get_balance_of_json_rpc( $public_address );
		$json     = \wp_json_encode( $json );
		$response = $this->request->post( $this->api_url, array( 'body' => $json ) );

		$balance = $this->get_balance_from_response( $response );
		return $balance;
	}

	/**
	 * @throws \Exception
	 * @return string
	 */
	public function get_owner( $nft ) {
		if ( empty( $nft->get_token_id() ) ) {
			throw new \InvalidArgumentException();
		}

		$token_id = new EthQ( $nft->get_token_id() );
		$owner    = $this->smart_contract->ownerOf( $token_id );

		if ( empty( $owner ) ) {
			return false;
		}

		return $owner->hexVal();
	}

	public function get_balance_of_json_rpc( $public_address ) {
		if ( empty( $public_address ) || ! is_string( $public_address ) ) {
			throw new \InvalidArgumentException();
		}

		$json               = $this->get_json_rpc_base();
		$function_signature = 'balanceOf(address)';
		$hash               = Keccak::Hash( $function_signature, 256 );
		$data               = '0x' . substr( $hash, 0, 8 );
		$data              .= $this->encode_type( 'address', $public_address );

		$json['params'][0]['data'] = $data;
		return $json;
	}

}
