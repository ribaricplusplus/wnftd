<?php

namespace WNFTD\Test\Utils;

trait NFT {
	public static $nfts = array(
		// This is a valid NFT from OpenSea: https://opensea.io/assets/0xe106c63e655df0e300b78336af587f300cff9e76/3684
		'exclusive_oxyan' => array(
			'contract_address' => '0xE106C63E655dF0E300b78336af587F300Cff9e76',
			'token_id'         => '3684',
			'contract_type'    => 'erc721',
			'owner'            => '0x461b5dd073be81cad6752bfcc355d5a252b8e910',
		),

		// This is a valid NFT from OpenSea: https://opensea.io/assets/0x5c6e2892ed14bd178f0928abce94c1373b8265eb/40
		'riddance'        => array(
			'contract_address' => '0x5C6e2892Ed14bD178F0928AbCe94C1373B8265eB',
			'token_id'         => '40',
			'contract_type'    => 'erc1155',
			'owner'            => '0x11d79df41dfa0bd51862e91f2e9395157fb36f3e',
		),
	);


	public function assertValidNFT( $object ) {
		$this->assertTrue( is_object( $object ) );
		$this->assertNotEmpty( $object->get_contract_address() );
		$this->assertContains( $object->get_contract_type(), array( 'erc721', 'erc1155' ) );
	}

}
