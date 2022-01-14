<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Product_Controller {

	public $auth;

	public $factory;

	  public function __construct( $auth, $factory = false ) {
	  	$this->auth = $auth;
		if ( empty( $factory ) ) {
			$this->factory = new Factory(); // Not ideal
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
				'customer_id' => $user
			)
		);
		$order->add_product( $product );
		$id = $order->save();
		$order = \wc_create_order(
			array(
				'order_id' => $id
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
		if ( \wc_customer_bought_product( '', $user_id, $product->get_id() ) ) {
			return true;
		}

		if ( ! $product->is_downloadable() ) {
			throw new \InvalidArgumentException();
		}

		if ( ! $this->is_nft_restricted( $product ) ) {
			return false;
		}

		$public_addresses = $this->auth->get_public_addresses( $user_id );

		if ( empty( $public_addresses ) ) {
			return false;
		}

		$required_nfts = $product->get_meta(  'wnftd_product_nft', false  );
		$required_nfts = \wp_list_pluck( $required_nfts, 'value' );

		// Is there any public address that owns all required NFTs?
		foreach( $public_addresses as $public_address ) {

			$has_access = true;

			foreach( $required_nfts as $nft_id ) {
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

	public function is_nft_restricted( $product ) {

		$required_nfts = $product->get_meta( 'wnftd_product_nft', false );

		if ( empty( $required_nfts ) ) {
			return false;
		}

		return true;
	}

}
