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

	/**
	 * @dataProvider valid_signatures_provider
	 */
	public function test_verify_public_address_verifies_for_valid_signature( $message, $signature, $address ) {
		$sut = new \WNFTD\Authentication();
		$this->assertTrue( $sut->verify_public_address( $address, $message, $signature ) );
	}

	public function test_ownership_transfer() {
		$sut = new \WNFTD\Authentication();

		$old_owner_id = $this->factory->user->create();
		$new_owner_id = $this->factory->user->create();

		$public_address = '0x1234';

		$sut->assign_public_address_to_user( $public_address, $old_owner_id );

		$addresses = \wp_list_pluck( \wp_get_object_terms( $old_owner_id, 'wnftd_public_address' ), 'name' );
		$this->assertContains( $public_address, $addresses );

		$sut->transfer_address_ownership( $old_owner_id, $new_owner_id, $public_address );

		$old_owner_addresses = \wp_get_object_terms( $old_owner_id, 'wnftd_public_address' );
		$new_owner_addresses = \wp_get_object_terms( $new_owner_id, 'wnftd_public_address' );

		$this->assertNotContains( $public_address, $old_owner_addresses );
		$this->assertContains( $public_address, $new_owner_addresses );
	}

	public function valid_signatures_provider() {
		return array(
			array(
				'This is my message',
				'0xd497d71f334eb3f154a76e2be0e28caff56943959445e0d23e129550c1873c8e65dd73047618f920c8857fa0fd0dfa49bb2306b882321399ac9a18646c69f70d1b',
				'0xe0e0abad1eb467bd8c74357c8a29645deed446af',
			),
			array(
				'Sign this message to authenticate with the application. Here is a nonce for security: 23b217c845',
				'0x56c227b3bbf27ed523bbea6b1f2ee49e52d76f0c9bdfcae23061b4c62acccd4b5f86e9b1da43481febc90e98f8e58dee61609a615665adaaee300aa1e450874f00',
				'0xa5bab0c4770c68b2377f055c4299b154a517a482',
			),
			array(
				'Sign this message to authenticate with the application. Here is a nonce for security: 50c6ff8da5',
				'0x083103c66dd08d23d6948bf48f35e4cf1447af7b226e984c7a1f77166c252cf90726ea442445248f61b9514164369a756af41dd093f4f36ea569b915e416bb4a1c',
				'0xdddce1a25a3900d671042c63942dee2c7cd99fc1',
			),
		);
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
