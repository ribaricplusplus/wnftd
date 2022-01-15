<?php

namespace WNFTD\Test;

class Authentication_Test extends \WP_UnitTestCase {
	// Random public address in the correct format
	const public_address = '0xb794f5ea0ba39494ce839613fffba74279579268';

	public function test_create_new_user() {
		$sut     = new \WNFTD\Authentication();
		$pa      = self::public_address;
		$user_id = $sut->create_new_user( $pa );
		$this->assertNotEmpty( $user_id );
		$user = \get_user_by( 'id', $user_id );
		$this->assertEquals( true, $user->exists() );
		$public_addresses = $sut->get_public_addresses( $user->ID );
		$this->assertContains( $pa, $public_addresses );
	}

	public function test_verify_public_address_verifies_for_valid_signature() {
		$message         = 'This is my message';
		$valid_signature = '0xd497d71f334eb3f154a76e2be0e28caff56943959445e0d23e129550c1873c8e65dd73047618f920c8857fa0fd0dfa49bb2306b882321399ac9a18646c69f70d1b';
		$public_address  = '0xe0e0abad1eb467bd8c74357c8a29645deed446af';
		$sut             = new \WNFTD\Authentication();
		$this->assertTrue( $sut->verify_public_address( $public_address, $message, $valid_signature ) );
	}

	public function test_verify_public_address_rejects_for_invalid_signature() {
		$message           = 'This is my message';
		$invalid_signature = '0xdaaaaaaaaaaeb3f154a76e2be0e28caff56943959445e0d23e129550c1873c8e65dd73047618f920c8857fa0fd0dfa49bb2306b882321399ac9a18646c69f70d1b';
		$public_address    = '0xe0e0abad1eb467bd8c74357c8a29645deed446af';
		$sut               = new \WNFTD\Authentication();
		$this->assertFalse( $sut->verify_public_address( $public_address, $message, $invalid_signature ) );
	}

	public function test_get_user_by_public_address() {
		$sut     = new \WNFTD\Authentication();
		$user_id = $sut->create_new_user( self::public_address );
		$this->assertNotEmpty( $user_id );
		$found_user_id = $sut->get_user_by_public_address( self::public_address );
		$this->assertEquals( $user_id, $found_user_id );
	}

	public function test_assign_public_address_to_user() {
		$sut  = new \WNFTD\Authentication();
		$user = $this->factory->user->create();
		$sut->assign_public_address_to_user( '0x1234', $user );
		$this->assertNotEmpty( \wp_get_object_terms( $user, 'wnftd_public_address' ) );
	}

}
