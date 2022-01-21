<?php

namespace WNFTD\Test;

use WNFTD\Product_Controller;
use WNFTD\Factory;

class Product_Controller_Test extends \WP_UnitTestCase {
	use Utils\Fixtures;

	public function set_up() {
		parent::set_up();

		// Register post types before each test.
		\WC_Post_types::register_post_types();
		\WC_Post_types::register_taxonomies();

		$this->wnftd_create_fixtures();
	}

	public function test_give_product_to_user() {
		$sut     = new Product_Controller( \WNFTD\auth() );
		$product = new \WC_Product( $this->sut_product );
		$user    = $this->sut_user;
		$sut->give_product_to_user( $product, $user );

		$this->assertTrue( \wc_customer_bought_product( '', $user, $product->get_id() ) );
	}

	public function test_grant_access_by_nft_works_for_user_who_owns_nft() {
		$owner_address = self::$owner_address;
		// User ID's public address is $owner_address.
		$user_id = $this->sut_user;

		$mock_factory = $this->get_mock_factory( $owner_address );

		$sut = new Product_Controller(
			\WNFTD\auth(),
			$mock_factory
		);

		$product = new \WC_Product( $this->sut_product );

		$sut->grant_access_by_nft( $user_id, $product );

		$available_downloads = \wc_get_customer_available_downloads( $user_id );
		$available_downloads = \wp_list_pluck( $available_downloads, 'product_id' );
		$this->assertContains( $product->get_id(), $available_downloads );
	}

	public function test_grant_access_rejects_for_user_who_does_not_own_nft() {
		$user_id      = \WNFTD\auth()->create_new_user( self::$random_address ); // Does not own the needed NFT
		$mock_factory = $this->get_mock_factory( self::$owner_address );
		$sut          = new Product_Controller(
			\WNFTD\auth(),
			$mock_factory
		);
		$product      = new \WC_Product( $this->sut_product );
		$sut->grant_access_by_nft( $user_id, $product );
		$this->assertNotContains( $product->get_id(), \wp_list_pluck( \wc_get_customer_available_downloads( $user_id ), 'product_id' ) );
	}

	public function test_grant_access_does_not_recheck_ownership_when_user_already_owns_product() {
		$product = new \WC_Product( $this->sut_product );
		$user_id = $this->sut_user;

		$mock_factory = $this->get_mock_factory( self::$owner_address );

		$sut = $this->getMockBuilder( Product_Controller::class )
			->setConstructorArgs( array( \WNFTD\auth(), $mock_factory ) )
			->onlyMethods( array( 'is_nft_restricted' ) )
			->getMock();

		$sut->expects( $this->never() )
			->method( 'is_nft_restricted' );

		$sut->give_product_to_user( $product, $user_id );

		$sut->grant_access_by_nft( $user_id, $product );
	}

	/**
	 * If supplied to Product_Controller, it will only ever use
	 * this stub as the NFT against which checks are made.
	 *
	 * @param string $owner_address This will be the owner of any NFT created.
	 */
	public function get_mock_factory( $owner_address ) {
		return new class( $owner_address ) {
			public function __construct( $owner_address ) {
				$this->owner_address = $owner_address;
			}

			// Returns a NFT stub whose owner is $owner_address.
			public function create_nft( $data = '' ) {
				return new class( $this->owner_address ) {
					public function __construct( $owner_address ) {
						$this->owner_address = $owner_address;
					}

					public function is_owner( $public_address ) {
						if ( $public_address === $this->owner_address ) {
							return true;
						}

						return false;
					}
				};
			}
		};
	}
}
