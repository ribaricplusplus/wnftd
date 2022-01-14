<?php
namespace WNFTD\Test\Remote;

use WNFTD\Test;
use WNFTD\Contracts;
use WNFTD\Factory;

/**
 * @group remote
 */
class NFT_Test extends \WP_UnitTestCase {
	use Test\Utils\NFT;

	public function test_ERC1155_owner() {
		$data = self::$nfts['riddance'];

		$nft = Factory::create_nft( $data );

		$contract = Factory::create_nft_contract( $nft );

		$balance = $contract->get_balance( $data['owner'], $nft );

		$this->assertIsInt( $balance );
	}

	public function test_ERC721_owner() {
		$data = self::$nfts['exclusive_oxyan'];
		$nft  = Factory::create_nft( $data );

		$contract = Factory::create_nft_contract( $nft );

		$owner = $contract->get_owner( $nft );

		$this->assertIsString( $owner );
		$this->assertTrue( strpos( $owner, '0x' ) === 0 );
	}
}
