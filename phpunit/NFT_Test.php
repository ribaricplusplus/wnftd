<?php

namespace WNFTD\Test;

use WNFTD\Factory;

class NFT_Test extends \WP_UnitTestCase {
	use Utils\NFT;

	// Random public address in the correct format
	const public_address = '0xb794f5ea0ba39494ce839613fffba74279579268';

	public static $nft_data = array(
		array(
			'contract_address' => '0x67D9417C9C3c250f61A83C7e8658daC487B56B09',
			'token_id'         => '6210',
			'contract_type'    => 'erc721',
		),
	);

	public static $nft_ids = array();

	public static function wpSetUpBeforeClass( $factory ) {
		$nft_1 = $factory->post->create(
			array(
				'post_title' => 'NFT 1',
				'post_type'  => 'wnftd-nft',
			)
		);
		\update_post_meta( $nft_1, 'token_id', self::$nft_data[0]['token_id'] );
		\update_post_meta( $nft_1, 'contract_address', self::$nft_data[0]['contract_address'] );
		\update_post_meta( $nft_1, 'contract_type', self::$nft_data[0]['contract_type'] );
		self::$nft_ids[] = $nft_1;
	}

	public function test_read_NFT_from_database_populated_by_wp() {
		$nft = Factory::create_nft( self::$nft_ids[0] );
		$this->assertValidNFT( $nft );
	}

	public function test_NFT_create() {
		$nft  = Factory::create_nft();
		$data = self::$nft_data[0];

		$token_id = '99999999';
		$nft->set_contract_address( $data['contract_address'] );
		$nft->set_token_id( $token_id );
		$nft->set_contract_type( $data['contract_type'] );
		$nft->save();

		$this->assertNotEmpty( $nft->get_id() );

		$nfts = \get_posts(
			array(
				'meta_key'   => 'token_id',
				'post_type'  => 'wnftd-nft',
				'meta_value' => $token_id,
				'fields'     => 'ids',
			)
		);

		$this->assertNotEmpty( $nfts );
		$this->assertValidNFT( Factory::create_nft( $nfts[0] ) );
	}

	public function test_NFT_update() {
		$nft     = Factory::create_nft( self::$nft_ids[0] );
		$address = '0x000123';
		$nft->set_contract_address( $address );
		$nft->save();

		$new_address = \get_post_meta( self::$nft_ids[0], 'contract_address', true );
		$this->assertEquals( $address, $new_address );
	}

	public function test_NFT_delete() {
		$nft      = Factory::create_nft( self::$nft_ids[0] );
		$token_id = $nft->get_token_id();
		$nft->delete();

		$post = \get_post( self::$nft_ids[0] );
		$this->assertEmpty( $post );

	}

	public function test_custom_NFT_metadata() {
		$nft = Factory::create_nft( self::$nft_ids[0] );
		$nft->add_meta_data( 'my_custom_meta_data', 123, true );
		$nft->save();

		$meta = \get_post_meta( self::$nft_ids[0], 'my_custom_meta_data', true );
		$this->assertEquals( 123, (int) $meta );
	}

	public function test_create_NFT_from_raw_data() {
		$nft = Factory::create_nft( self::$nft_data[0] );
		$this->assertValidNFT( $nft );
	}
}
