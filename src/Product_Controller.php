<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Product_Controller {

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
		$order->save();
		$order->set_status( 'completed' );
		$order->save();
	}

}
