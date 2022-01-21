<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;

class Product_Controller implements Initializable {

	public $auth;

	public $factory;

	public function init() {
		\add_filter( 'woocommerce_is_purchasable', array( $this, 'woocommerce_is_purchasable' ), 10, 2 );
	}

	public function woocommerce_is_purchasable( $purchasable, $product ) {
		if ( ! $purchasable ) {
			return $purchasable;
		}

		if ( $this->is_nft_restricted( $product ) ) {
			return false;
		}

		return $purchasable;
	}

	public function __construct( $auth, $factory = false ) {
		$this->auth = $auth;
		if ( empty( $factory ) ) {
			$this->factory = new Factory(); // Factory should never have been static in the first place.
		} else {
			$this->factory = $factory;
		}
	}

	/**
	 * @param \WC_Product $product
	 * @param int $user
	 */
	public function give_product_to_user( $product, $user ) {
		$order = \wc_create_order(
			array(
				'customer_id' => $user,
			)
		);
		$order->add_product( $product );
		$id    = $order->save();
		$order = \wc_create_order(
			array(
				'order_id' => $id,
			)
		);
		$order->set_status( 'completed' );
		$order->save();
	}

	/**
	 * Checks if user has access to NFT product. Grants access to product if user
	 * owns the configured NFTs.
	 *
	 * @param int $user_id
	 * @param |WC_Product $product
	 * @throws \Exception
	 * @return bool Whether user has access.
	 */
	public function grant_access_by_nft( $user_id, $product ) {
		if ( ! $product->is_downloadable() ) {
			throw new \InvalidArgumentException();
		}

		if ( $this->user_can_download_product( $user_id, $product ) ) {
			return true;
		}

		if ( ! $this->is_nft_restricted( $product ) ) {
			return false;
		}

		$public_addresses = $this->auth->get_public_addresses( $user_id );

		if ( empty( $public_addresses ) ) {
			return false;
		}

		$required_nfts = $this->get_required_nfts_ids( $product );

		// Is there any public address that owns all required NFTs?
		foreach ( $public_addresses as $public_address ) {

			$has_access = true;

			foreach ( $required_nfts as $nft_id ) {
				$nft = $this->factory->create_nft( $nft_id );

				if ( ! $nft->is_owner( $public_address ) ) {
					$has_access = false;
					break;
				}
			}

			if ( $has_access ) {
				$this->give_product_to_user( $product, $user_id );
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $user_id
	 * @param \WC_Product $product
	 * @return bool
	 */
	public function user_can_download_product( $user_id, $product ) {
		$results = \wc_get_customer_download_permissions( $user_id );
		foreach ( $results as $result ) {
			if ( (int) $result->product_id === (int) $product->get_id() ) {
				return true;
			}
		}

		return false;
	}

	public function get_required_nfts_ids( $product ) {
		$required_nfts = $product->get_meta( 'wnftd_product_nft', false );
		$required_nfts = \wp_list_pluck( $required_nfts, 'value' );
		return $required_nfts;
	}

	public function is_nft_restricted( $product ) {
		if ( is_numeric( $product ) ) {
			$product = new \WC_Product( $product );
		}

		$required_nfts = $product->get_meta( 'wnftd_product_nft', false );

		if ( empty( $required_nfts ) ) {
			return false;
		}

		return true;
	}

}
