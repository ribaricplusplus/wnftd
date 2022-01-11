<?php

namespace WNFTD\Test\Utils;

trait NFT {

	public function assertValidNFT( $object ) {
		$this->assertTrue( is_object( $object ) );
		$this->assertNotEmpty( $object->get_contract_address() );
		$this->assertContains( $object->get_contract_type(), array( 'erc721', 'erc1155' ) );
	}

}
