<?php

namespace WNFTD\Test\Utils;

use WNFTD\Factory;

trait Fixtures {
	public static $owner_address = '0x461b5dd073be81cad6752bfcc355d5a252b8e910';
	public static $random_address = '0x11d79df41dfa0bd51862e91f2e9395157fb36f3e';

	public $sut_product;
	public $sut_nft;
	public $sut_user;

	public function wnftd_create_fixtures() {
		$product = new \WC_Product();

		$download = new \WC_Product_Download();
		$download->set_name( 'Test download' );
		$download->set_file( \plugin_dir_path( \WNFTD_FILE ) . 'phpunit/fixtures/testfile.txt' );

		$nft = Factory::create_nft();
		$nft->set_props(
			array(
				'contract_address' => '0xE106C63E655dF0E300b78336af587F300Cff9e76',
				'token_id'         => '3684',
				'contract_type'    => 'erc721',
				'fake_owner' => self::$owner_address
			)
		);
		$nft->save();
		$this->sut_nft = $nft->get_id();

		$product->set_props(
			array(
				'name' => 'Test',
				'downloadable' => true,
				'downloads' => array(
					$download
				)
			)
		);
		$product->add_meta_data( 'wnftd_product_nft', $nft->get_id() );
		$this->sut_product = $product->save();

		$this->sut_user = \WNFTD\auth()->create_new_user( self::$owner_address );
	}
}
