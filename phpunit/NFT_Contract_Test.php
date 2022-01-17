<?php

namespace WNFTD\Test;

use WNFTD\Factory;
use Ethereum\DataType;
use WNFTD\Contracts;

class NFT_Contract_Test extends \WP_UnitTestCase {
	use Utils\NFT, Utils\Request;

	public function test_ERC721_owner() {
		$data = self::$nfts['exclusive_oxyan'];
		$nft  = Factory::create_nft( $data );

		$contract = Factory::create_nft_contract( $nft );

		$contract->smart_contract = $this->getMockBuilder( \stdClass::class )
		->addMethods( array( 'ownerOf' ) )
		->getMock();

		$contract->smart_contract->method( 'ownerOf' )
		->willReturn( new DataType\EthD( $data['owner'] ) );

		$this->assertNotEmpty( $contract->get_owner( $nft ) );
	}

	public function test_ERC1155_json_rpc_data() {
		$data         = self::$nfts['riddance'];
		$encoded_data = '0x00fdd58e00000000000000000000000011d79df41dfa0bd51862e91f2e9395157fb36f3e0000000000000000000000000000000000000000000000000000000000000028';

		$contract = Factory::create_nft_contract( $data );

		$json = $contract->get_balance_of_json_rpc( $data['owner'], $data['token_id'] );

		$this->assertEquals( $encoded_data, $json['params'][0]['data'] );
	}

	/**
	 * @dataProvider invalid_balance_from_response_data
	 */
	public function test_get_balance_from_response_with_invalid_data( $response ) {
		$this->expectException( \InvalidArgumentException::class );

		$sut = new Contracts\ERC1155();
		$sut->get_balance_from_response( $response );
	}

	/**
	 * @dataProvider valid_balance_from_response_data
	 */
	public function test_get_balance_from_response_with_valid_data( $response, $expected_balance ) {
		$sut = new Contracts\ERC1155();

		$this->assertEquals( $expected_balance, $sut->get_balance_from_response( $response ) );
	}

	/**
	 * @dataProvider valid_balance_from_response_data
	 */
	public function test_ERC721_owner_without_token_id( $response, $balance ) {
		$nft_data = array(
			'contract_address' => '0xE106C63E655dF0E300b78336af587F300Cff9e76',
			'contract_type'    => 'erc721',
		);

		$nft = Factory::create_nft( $nft_data );

		$owner = '0x461b5dd073be81cad6752bfcc355d5a252b8e910';

		$mock_request = $this->createStub( \WNFTD\Request::class );
		$mock_request->method( 'post' )
			->willReturn( $response );

		$contract          = Factory::create_nft_contract( $nft );
		$contract->request = $mock_request;

		if ( $balance > 0 ) {
			$this->assertTrue( $contract->is_owner( $owner ) );
		} else {
			$this->assertFalse( $contract->is_owner( $owner ) );
		}
	}

	public function test_ERC721_json_rpc_data_when_no_token_id() {
		$data         = self::$nfts['exclusive_oxyan'];
		$encoded_data = '0x70a08231000000000000000000000000461b5dd073be81cad6752bfcc355d5a252b8e910';

		$contract = Factory::create_nft_contract( $data );
		$json     = $contract->get_balance_of_json_rpc( $data['owner'] );

		$this->assertEquals( $encoded_data, $json['params'][0]['data'] );
	}

	public function valid_balance_from_response_data() {
		$base = array(
			'id'      => '797cd950-3f7c-4fcc-8b0d-f243766e5eb5',
			'jsonrpc' => '2.0',
		);

		$results = array( '0x0000000000000000000000000000000000000000000000000000000000000001', '0x0000000000000000000000000000000000000000000000000000000000000002', '0x00000000000000000000000000000000000000000000000000000000000000010', '0x0000000000000000000000000000000000000000000000000000000000000000' );

		foreach ( $results as $result ) {
			$data[] = array(
				$this->create_response(
					array(
						'body' => \wp_json_encode( array_merge( $base, array( 'result' => $result ) ) ),
					)
				),
				hexdec( $result ),
			);
		}

		return $data;
	}

	public function invalid_balance_from_response_data() {
		return array(
			array(
				new \WP_Error(),
			),
			array(
				1,
			),
			array(
				array( 'code' => 400 ),
			),
		);
	}

}
