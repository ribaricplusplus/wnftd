<?php

namespace WNFTD\Test;

use WNFTD\Product_Controller;

class Product_Controller_Test extends \WP_UnitTestCase {
	public static $product_ids = array();
	public static $user_ids = array();

	public static function wpSetUpBeforeClass( $factory ) {
		$product = new \WC_Product();
		$product->set_name( 'Test' );
		$product->save();
		self::$product_ids[] = $product->get_id();

		self::$user_ids[] = \wc_create_new_customer( 'wnftd-test@example.com' );
	}

	public function test_give_product_to_user() {
		$sut = new Product_Controller();
		$product = new \WC_Product( self::$product_ids[0] );
		$user = self::$user_ids[0];
		$sut->give_product_to_user($product, $user );

		$this->assertTrue( \wc_customer_bought_product( '', $user, $product->get_id() ) );
	}
}
