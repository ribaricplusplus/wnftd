<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Authentication implements Interfaces\Initializable {
	public function init() {}

	public function verify_public_address( $public_address, $message ) {
		// TODO: Implement
	}

	/**
	 * @param string $public_address
	 * @return \WP_User|null
	 */
	public function get_user_by_public_address( $public_address ) {
		// TODO: Implement
	}

	/**
	 * @param int    $old_owner User ID of the old owner.
	 * @param int    $new_owner User ID of the new owner.
	 * @param string $public_address
	 */
	public function transfer_address_ownership( $old_owner, $new_owner, $public_address ) {
		$this->unassign_public_address_from_user( $public_address, $old_owner );

		$this->assign_public_address_to_user( $public_address, $new_owner );
	}

	/**
	 * @param string $public_address
	 * @param int    $user_id
	 */
	public function assign_public_address_to_user( $public_address, $user_id ) {
		$public_addresses = get_user_option( 'wnftd_public_addresses', $user_id );
		if ( empty( $public_addresses ) ) {
			$public_addresses = array();
		}
		$public_addresses[] = $public_address;
		$public_addresses   = array_values( array_unique( $public_addresses ) );
		update_user_option( $user_id, 'wnftd_public_addresses', $public_addresses );
	}

	/**
	 * @param string $public_address
	 * @param int    $user_id
	 */
	public function unassign_public_address_from_user( $public_address, $user_id ) {
		$public_addresses = get_user_option( 'wnftd_public_addresses', $user_id );
		$public_addresses = array_filter(
			$public_addresses,
			function( $address ) use ( $public_address ) {
				if ( $address !== $public_address ) {
					return true;
				}
				return false;
			}
		);
		update_user_option( $user_id, 'wnftd_public_addresses', $public_addresses );
	}

	/**
	 * @param \WP_User|int $user
	 */
	public function log_in_user( $user ) {
		if ( is_object( $user ) ) {
			$user = $user->ID;
		}
		wp_set_auth_cookie( $user->ID, false );
		wp_set_current_user( $user->ID );
	}

	/**
	 * @param string $public_address
	 * @throws \Exception
	 * @return int User ID.
	 */
	public function create_new_user( $public_address ) {
		$email   = sprintf( '%s@example.com', \wp_generate_uuid4() );
		$user_id = \wc_create_new_customer( $email );
		if ( is_wp_error( $user_id ) ) {
			throw new \Exception( 'Failed to create user' );
		}
		\update_user_option( $user_id, 'wnftd_public_addresses', array( $public_address ) );
		return $user_id;
	}

}
