<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use Ethereum\EcRecover;

class Authentication implements Interfaces\Initializable {
	public function init() {
		\add_action( 'saved_wnftd_public_address', array( $this, 'force_lowercase_term_name' ) );
	}

	/**
	 * @param string $public_address
	 * @param string $message
	 * @param string $signature
	 * @return bool Whether the $signature for $message is by $public address.
	 */
	public function verify_public_address( $public_address, $message, $signature ) {
		try {
			$public_address = strtolower( $public_address );
			$recovered      = EcRecover::personalEcRecover( $message, $signature );
			if ( \WNFTD\public_addresses_equal( $public_address, $recovered ) ) {
				return true;
			}
			return false;
		} catch ( \Exception $e ) {
			trigger_error(
				'Exception occurred while recovering public address. This could happen because of a missing PHP extension or a malformed signature / message.',
				\E_USER_NOTICE
			);
			return false;
		}
	}

	/**
	 * @param string $public_address
	 * @return int|null
	 */
	public function get_user_by_public_address( $public_address ) {
		$public_address = strtolower( $public_address );

		$term = $this->get_public_address_term( $public_address );

		if ( empty( $term ) ) {
			return null;
		}

		$ids = \get_objects_in_term( $term->term_id, 'wnftd_public_address' );

		if ( empty( $ids ) ) {
			return null;
		}

		return $ids[0];
	}

	/**
	 * @param string $public_address
	 * @return \WP_Term|null
	 */
	public function get_public_address_term( $public_address ) {
		$term = \get_term_by( 'name', $public_address, 'wnftd_public_address' );
		if ( empty( $term ) ) {
			return null;
		}
		return $term;
	}

	/**
	 * @param int    $old_owner User ID of the old owner.
	 * @param int    $new_owner User ID of the new owner.
	 * @param string $public_address
	 */
	public function transfer_address_ownership( $old_owner, $new_owner, $public_address ) {
		$public_address = strtolower( $public_address );

		$this->unassign_public_address_from_user( $public_address, $old_owner );

		$this->assign_public_address_to_user( $public_address, $new_owner );
	}

	/**
	 * @param string $public_address
	 * @param int    $user_id
	 */
	public function assign_public_address_to_user( $public_address, $user_id ) {
		$public_address = strtolower( $public_address );

		if ( ! \term_exists( $public_address ) ) {
			\wp_insert_term( $public_address, 'wnftd_public_address' );
		}

		\wp_add_object_terms( $user_id, $public_address, 'wnftd_public_address' );
	}

	/**
	 * @param string $public_address
	 * @param int    $user_id
	 */
	public function unassign_public_address_from_user( $public_address, $user_id ) {
		\wp_remove_object_terms( $user_id, $public_address, 'wnftd_public_address' );
	}

	/**
	 * @param \WP_User|int $user
	 */
	public function log_in_user( $user ) {
		if ( is_object( $user ) ) {
			$user = $user->ID;
		}
		wp_set_current_user( $user );
		wp_set_auth_cookie( $user, true );
	}

	/**
	 * @param string $public_address
	 * @throws \Exception
	 * @return int User ID.
	 */
	public function create_new_user( $public_address ) {
		$public_address = strtolower( $public_address );

		$email = sprintf( '%s@example.com', \wp_generate_uuid4() );
		if ( $this->public_address_exists( $public_address ) ) {
			throw new \Exception( 'Cannot create a new user for a public address that already exists.' );
		}

		$term = \wp_insert_term( $public_address, 'wnftd_public_address' );

		if ( is_wp_error( $term ) ) {
			throw new \Exception( 'Failed to create user' );
		}

		$user_id = \wc_create_new_customer( $email );

		if ( is_wp_error( $user_id ) ) {
			\wp_delete_term( $term['term_id'] );
			throw new \Exception( 'Failed to create user.' );
		}

		$result = \wp_set_object_terms( $user_id, $term['term_id'], 'wnftd_public_address' );

		if ( is_wp_error( $result ) ) {
			\wp_delete_user( $user_id );
			\wp_delete_term( $term['term_id'] );
			throw new \Exception( 'Failed to create user.' );
		}

		return $user_id;
	}

	public function delete_user( $user_id ) {
		$terms = \wp_get_object_terms( $user_id, 'wnftd_public_address' );
		foreach ( $terms as $term ) {
			$val = \wp_delete_term( $term, 'wnftd_public_address' );

			if ( $val === false ) {
				throw new \Exception( 'Failed to delete user.' );
			}
		}

		\wp_delete_user( $user_id );
	}

	public function public_address_exists( $public_address ) {
		$public_address = strtolower( $public_address );

		return (bool) get_term_by( 'name', $public_address, 'wnftd_public_address' );
	}

	/**
	 * @param int $user_id
	 * @throws \Exception
	 * @return string[]
	 */
	public function get_public_addresses( $user_id ) {
		if ( empty( $user_id ) ) {
			return array();
		}

		$terms = \wp_get_object_terms( $user_id, 'wnftd_public_address' );

		if ( is_wp_error( $terms ) ) {
			throw new \Exception();
		}

		return \wp_list_pluck( $terms, 'name' );
	}

	public function force_lowercase_term_name( $term_id ) {
		// This will not be an infinite loop because next time the term is
		// saved, the regex will not match.
		$term          = \get_term( $term_id );
		$has_uppercase = preg_match( '/[A-Z]/', $term->name );

		if ( $has_uppercase ) {
			$updated = \wp_update_term( $term->term_id, 'wnftd_public_address', array( 'name' => strtolower( $term->name ) ) );
			if ( ! $updated ) {
				throw new \Exception( 'Term update failed.' );
			}
		}
	}

}
