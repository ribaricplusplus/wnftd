<?php

namespace WNFTD\Test\REST;

use WNFTD\Test;

class Products_Test extends \WP_Test_REST_TestCase {
	use Test\Utils\Fixtures, Utils;

	public function set_up() {
		parent::set_up();

		// Register post types before each test.
		\WC_Post_types::register_post_types();
		\WC_Post_types::register_taxonomies();

		$this->wnftd_create_fixtures();
	}

	public function test_valid_nft_owner_gets_product_access() {
		// Owner of self::$owner_address.
		\wp_set_current_user( $this->sut_user );
		$request = $this->_get_request();
		$response = \rest_get_server()->dispatch( $request );
		$this->assertNotWPError( $response );
		$this->assertNotEquals( $response->status, 401 );
		$transient_name = 'wc_customer_bought_product_' . md5( $this->sut_user );

		// A bit hacky, but the WC transient is being flaky.
		// This makes it so that \wc_customer_bought_product never uses cache.
		add_filter( "transient_{$transient_name}", '__return_null' );

		$bought = \wc_customer_bought_product( '', $this->sut_user, $this->sut_product );
		$this->assertTrue( $bought );
	}

	public function test_access_granted_when_product_already_owned() {
		$user_id = $this->sut_user;
		$product_id = $this->sut_product;
		\wp_set_current_user( $user_id );
		\WNFTD\product_controller()->give_product_to_user( new \WC_Product( $product_id ), $user_id );
		$request = $this->_get_request();
		$response = \rest_get_server()->dispatch( $request );
		$this->assertNotWPError( $response );
		$data = $response->get_data();
		$this->assertEquals( 'access_granted', $data['code'] );
	}

	public function test_invalid_request_gets_permission_denied() {
		$user_id = \WNFTD\auth()->create_new_user( self::$random_address ); // Does not own the needed NFT
		\wp_set_current_user( $user_id );
		$request = $this->_get_request();
		$response = \rest_get_server()->dispatch( $request );
		$this->assertEquals( $response->status, 401 );
	}

	protected function _get_request( $params = array() ) {
		$nonce    = \wp_create_nonce( 'wnftd_product_download' );

		$defaults = array(
			'id' => $this->sut_product,
			'_wpnonce' => $nonce
		);

		$params   = \wp_parse_args(
			$params,
			$defaults,
		);

		return $this->get_request( '/wnftd/v1/products/download', $params );
	}

}
