<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use Ethereum\Ethereum;

class Factory {

	/*
	|--------------------------------------------------------------------------
	| Factory methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * $data can be an array with the following properties:
	 * - contract_address: string
	 * - contract_type: 'erc721' | 'erc1155'
	 * - token_id: string
	 *
	 * @param  NFT|array|int $data
	 * @throws \Exception
	 * @return NFT
	 */
	public static function create_nft( $data = 0 ) {
		if ( is_array( $data ) ) {
			$instance = new NFT();
			$instance->set_props(
				\WNFTD\filter_keys( $data, $instance->get_data_keys() )
			);
		} elseif ( is_numeric( $data ) || is_a( $data, __NAMESPACE__ . '\\NFT' ) ) {
			$instance = new NFT( $data );
		} else {
			throw new \InvalidArgumentException();
		}

		return $instance;
	}

	/**
	 * @param NFT|array $data Contains 'contract_address' and 'contract_type'.
	 * @throws \Exception
	 * @return NFT_Contract
	 */
	public static function create_nft_contract( $data ) {
		if ( is_a( $data, __NAMESPACE__ . '\\NFT' ) ) {
			$data = array(
				'contract_address' => $data->get_contract_address(),
				'contract_type'    => $data->get_contract_type(),
				'network' => $data->get_network()
			);
		}

		if ( empty( $data['contract_address'] || empty( $data['contract_type'] ) ) ) {
			throw new \InvalidArgumentException();
		}

		$data = \wp_parse_args(
			$data,
			array(
				'network' => 'polygon',
			)
		);

		switch ( $data['contract_type'] ) {
			case 'erc721':
				$abi            = json_decode( file_get_contents( plugin_dir_path( \WNFTD_FILE ) . 'contracts/erc721abi.json' ) );
				$smart_contract = new \Ethereum\SmartContract(
					$abi,
					$data['contract_address'],
					self::create_ethereum( $data['network'] )
				);
				$instance       = new Contracts\ERC721( $smart_contract, self::create_request(), $data['contract_address'], $data['network'] );
				break;
			case 'erc1155':
				$instance = new Contracts\ERC1155( $data['contract_address'], $data['network'] );
				break;
			default:
				throw new \UnexpectedValueException();
		}

		return $instance;
	}

	/**
	 * @throws \Exception
	 */
	public static function create_ethereum( $network = 'ethereum' ) {
		$api_key = \WNFTD\get_api_key( $network );

		if ( ! empty( $api_key ) ) {
			return new Ethereum( $api_key );
		}

		return new Ethereum();
	}

	public static function create_request() {
		return new \WNFTD\Request();
	}
}
