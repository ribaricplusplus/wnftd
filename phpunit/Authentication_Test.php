<?php

namespace WNFTD\Test;

class Authentication_Test extends \WP_UnitTestCase {

	public function test_create_new_user() {
		$sut     = new \WNFTD\Authentication();
		$pa      = '0x1234';
		$user_id = $sut->create_new_user( $pa );
		$this->assertNotEmpty( $user_id );
		$user = \get_user_by( 'id', $user_id );
		$this->assertEquals( true, $user->exists() );
		$public_addresses = \get_user_option( 'wnftd_public_addresses', $user->ID );
		$this->assertContains( $pa, $public_addresses );
	}

}
