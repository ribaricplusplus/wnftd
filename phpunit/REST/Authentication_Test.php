<?php

namespace WNFTD\Test\REST;

class Authentication_Test extends \WP_Test_REST_TestCase {
	use Utils;

	public static $user_id;

	public static $original_auth;

	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		self::$user_id       = $factory->user->create();
		self::$original_auth = \WNFTD\instance()->auth;
	}

	public function tear_down() {
		parent::tear_down();
		\WNFTD\instance()->auth = self::$original_auth;
	}

	public function test_add_public_address_to_logged_in_user() {
		\wp_set_current_user( self::$user_id );
		\WNFTD\instance()->auth = $this->getMockBuilder( \WNFTD\Authentication::class )
			->getMock();
		\WNFTD\instance()->auth->method( 'verify_public_address' )
			->willReturn( true );
		$nonce    = \wp_create_nonce( 'wnftd_auth' );
		$request  = $this->get_request(
			'/wnftd/v1/auth',
			array(
				'public_address' => '0x1234',
				'_wpnonce'       => $nonce,
			)
		);
		$response = \rest_get_server()->dispatch( $request );
		$this->assertNotWPError( $response );
		$data = $response->get_data();
		$this->assertSame( $data['code'], 'added_public_address' );
	}

	public function test_user_already_authenticated() {
		\wp_set_current_user( self::$user_id );
		\WNFTD\instance()->auth = $this->getMockBuilder( \WNFTD\Authentication::class )
			->getMock();
		\WNFTD\instance()->auth->method( 'verify_public_address' )
			->willReturn( true );
		\WNFTD\instance()->auth->method( 'get_user_by_public_address' )
			->willReturn( wp_get_current_user() );
		$request  = $this->_get_request();
		$response = \rest_get_server()->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( 'already_authenticated', $data['code'] );
	}

	public function test_transfer_ownership() {
		\wp_set_current_user( self::$user_id );
		$old_owner_id   = $this->factory->user->create();
		$public_address = '0x1234';
		\WNFTD\auth()->assign_public_address_to_user( $public_address, $old_owner_id );

		$old_owner_addresses = \get_user_option( 'wnftd_public_addresses', $old_owner_id );
		$new_owner_addresses = \get_user_option( 'wnftd_public_addresses', self::$user_id );
		if ( empty( $new_owner_addresses ) ) {
			$new_owner_addresses = array();
		}
		$this->assertContains( $public_address, $old_owner_addresses );
		$this->assertNotContains( $public_address, $new_owner_addresses );

		\WNFTD\instance()->auth = $this->getMockBuilder( \WNFTD\Authentication::class )
			->onlyMethods( array( 'get_user_by_public_address', 'verify_public_address' ) )
			->getMock();
		\WNFTD\instance()->auth->method( 'verify_public_address' )
			->willReturn( true );
		\WNFTD\instance()->auth->method( 'get_user_by_public_address' )
			->willReturn( \get_user_by( 'id', $old_owner_id ) );

		$request  = $this->_get_request( array( 'public_address' => $public_address ) );
		$response = \rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$old_owner_addresses = \get_user_option( 'wnftd_public_addresses', $old_owner_id );
		$new_owner_addresses = \get_user_option( 'wnftd_public_addresses', self::$user_id );
		$this->assertContains( $public_address, $new_owner_addresses );
		$this->assertNotContains( $public_address, $old_owner_addresses );
		$this->assertEquals( 'ownership_transferred', $data['code'] );
	}

	protected function _get_request( $params = array() ) {
		$nonce    = \wp_create_nonce( 'wnftd_auth' );
		$defaults = array(
			'public_address' => '0x1234',
			'_wpnonce'       => $nonce,
		);
		$params   = \wp_parse_args(
			$params,
			$defaults,
		);
		return $this->get_request( '/wnftd/v1/auth', $params );
	}

}
