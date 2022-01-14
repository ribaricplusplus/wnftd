<?php

namespace WNFTD\Admin\Meta_Boxes;

defined( 'ABSPATH' ) || exit;

// Provided by WooCommerce
use Automattic\Jetpack\Constants;

trait Utils {
	public $saved;

	/**
	 * @param int     $post_id WP post id.
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function should_save( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || $this->saved ) {
			return false;
		}

		if ( Constants::is_true( 'DOING_AUTOSAVE' ) || \is_int( \wp_is_post_revision( $post ) ) || \is_int( \wp_is_post_autosave( $post ) ) ) {
			return false;
		}

		if ( empty( $_POST['wnftd_meta_nonce'] ) || ! \wp_verify_nonce( \wp_unslash( $_POST['wnftd_meta_nonce'] ), 'wnftd_save_nonce' ) ) {
			return false;
		}

		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}
}
