<?php

namespace WNFTD\Test\Admin;

use WNFTD\Factory;
use WNFTD\Test;
use WNFTD\Admin\Meta_Boxes;

class Meta_Boxes_Test extends \WP_UnitTestCase {
	use Test\Utils\NFT;

	public static $nft_ids = array();

	public static function wpSetUpBeforeClass( $factory ) {
		self::$nft_ids[] = $factory->post->create(
			array(
				'post_type'  => 'wnftd-nft',
				'post_title' => 'An NFT',
			)
		);

		$admin = $factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		\wp_set_current_user( $admin );
	}

	public function test_nft_data_saving() {
		$post_id = self::$nft_ids[0];
		$nft     = Factory::create_nft( $post_id );
		$this->assertEmpty( $nft->get_token_id() );

		$data                      = self::$nfts['riddance'];
		$_POST['token_id']         = $data['token_id'];
		$_POST['contract_address'] = $data['contract_address'];
		$_POST['contract_type']    = $data['contract_type'];
		$_POST['wnftd_meta_nonce'] = \wp_create_nonce( 'wnftd_save_nonce' );

		$sut = new Meta_Boxes\NFT();

		$_POST['post_ID'] = $post_id;

		$sut->save( $post_id, \get_post( $post_id ) );

		$nft = Factory::create_nft( $post_id );

		$this->assertNotEmpty( $nft->get_token_id() );
		$this->assertEquals( $data['token_id'], $nft->get_token_id() );
		$this->assertEquals( $data['contract_address'], $nft->get_contract_address() );
		$this->assertEquals( $data['contract_type'], $nft->get_contract_type() );
	}
}
