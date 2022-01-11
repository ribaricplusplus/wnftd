<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

final class Factory {

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
				$data
			);
		} elseif ( is_numeric( $data ) || is_a( $data, __NAMESPACE__ . '\\NFT' ) ) {
			$instance = new NFT( $data );
		} else {
			throw new \InvalidArgumentException();
		}

		return $instance;
	}

	/**
	 * @param NFT $data
	 * @throws \Exception
	 * @return NFT_Contract
	 */
	public static function create_nft_contract( $data ) {
		$cdata = array();
		if ( is_a( $data, __NAMESPACE__ . '\\NFT' ) ) {
			$cdata['contract_address'] = $data->get_contract_address();
			$cdata['contract_type']    = $data->get_contract_type();
		} else {
			throw new \InvalidArgumentException();
		}

		switch ( $cdata['contract_type'] ) {
			case 'erc721':
				$instance       = new Contracts\ERC721( $cdata['contract_address'] );
				$abi            = json_decode( file_get_contents( plugin_dir_path( \WNFTD_FILE ) . 'contracts/erc721abi.json' ), true );
				$smart_contract = new \Ethereum\SmartContract(
					$abi,
					$cdata['contract_address'],
					\WNFTD\instance()->ethereum
				);
				break;
			case 'erc1155':
				$instance       = new Contracts\ERC1155( $cdata['contract_address'] );
				$abi            = json_decode( file_get_contents( plugin_dir_path( \WNFTD_FILE ) . 'contracts/erc1155abi.json' ), true );
				$smart_contract = new \Ethereum\SmartContract(
					$abi,
					$cdata['contract_address'],
					\WNFTD\instance()->ethereum
				);
				break;
			default:
				throw new \UnexpectedValueException();
		}

		$instance->smart_contract = $smart_contract;

		return $instance;
	}
}
