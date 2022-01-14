<?php

namespace WNFTD\Admin\Meta_Boxes;

defined( 'ABSPATH' ) || exit;

use WNFTD\Interfaces\Initializable;
use WNFTD\Factory;

class NFT implements Initializable {
	use Utils {
		Utils::should_save as trait_should_save;
	}

	public function init() {
		\add_action( 'wp_after_insert_post', array( $this, 'save' ), 10, 2 );
	}

	public function output() {
		\WNFTD\view( 'meta-boxes/nft-data' );
	}

	public function should_save( $post_id, $post ) {
		if ( ! $this->trait_should_save( $post_id, $post ) ) {
			return false;
		}

		if ( $post->post_type !== 'wnftd-nft' ) {
			return false;
		}

		return true;
	}

	/**
	 * @param int     $post_id WP post id.
	 * @param \WP_Post $post Post object.
	 */
	public function save( $post_id, $post ) {
		if ( ! $this->should_save( $post_id, $post ) ) {
			return;
		}

		$nft = Factory::create_nft( $post_id );

		// Examine $_POST data and set props.
		$nft->set_props(
			array(
				'token_id'         => $_POST['token_id'] ?? \WNFTD\clean_unslash( $_POST['token_id'] ),
				'contract_address' => $_POST['contract_address'] ?? \WNFTD\clean_unslash( $_POST['contract_address'] ),
				'contract_type'    => $_POST['contract_type'] ?? \WNFTD\clean_unslash( $_POST['contract_type'] ),
				'fake_owner'    => $_POST['fake_owner'] ?? \WNFTD\clean_unslash( $_POST['fake_owner'] ),
			)
		);

		$nft->save();

		$this->saved = true; // See Utils::should_save
	}

}
