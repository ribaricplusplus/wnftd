<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use Elliptic\EC;
use kornrunner\Keccak;
use Ethereum\EcRecover;

/**
 * TODO: All of the public address functionality here should be extracted into
 * a separate class that wraps a single public address.
 */
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
			$recovered      = $this->_recover_public_address( $public_address, $message, $signature );
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
	 * @throws \Exception
	 * @param string $public_address
	 * @param string $message
	 * @param string $signature
	 * @return string Whether the $signature for $message is by $public address.
	 */
	public function _recover_public_address( $public_address, $message, $signature ) {
		$ec           = new EC( 'secp256k1' );
		$sign         = array(
			'r' => substr( $signature, 2, 64 ),
			's' => substr( $signature, 66, 64 ),
		);
		$message_hash = '0x' . Keccak::hash( EcRecover::personalSignAddHeader( $message ), 256 );

		// Recovery parameter must be a number between 0 and 3. See https://github.com/indutny/elliptic/blob/43ac7f230069bd1575e1e4a58394a512303ba803/lib/elliptic/ec/index.js#L226
		for ( $recid = 0; $recid < 4; ++$recid ) {
			try {
				$pub_key           = $ec->recoverPubKey( $message_hash, $sign, $recid );
				$recovered_address = '0x' . substr( Keccak::hash( substr( hex2bin( $pub_key->encode( 'hex' ) ), 1 ), 256 ), 24 );
				if ( ! \WNFTD\public_addresses_equal( $public_address, $recovered_address ) ) {
					continue;
				}
				return $recovered_address;
			} catch ( \Exception $e ) {
				continue;
			}
		}

		throw new \Exception( 'Recovery failed.' );
	}


	/**
	 * @param string $public_address
	 * @return \WP_User|null
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

		return \get_user_by( 'id', absint( $ids[0] ) );
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
		try {
			$public_address = strtolower( $public_address );

			$uid   = \wp_generate_uuid4();
			$email = sprintf( '%s@example.com', $uid );

			if ( $this->public_address_exists( $public_address ) ) {
				$this->unassign_public_address_from_all_users( $public_address );
				$term    = $this->get_public_address_term( $public_address );
				$term_id = $term->term_id;
			} else {
				$term = \wp_insert_term( $public_address, 'wnftd_public_address' );
				if ( is_wp_error( $term ) ) {
					throw new \Exception( 'Failed to save public address.' );
				}
				$term_id = $term['term_id'];
			}

			$user_id = \WNFTD\call( 'wc_create_new_customer', array( $email, $uid, \wp_generate_password() ) );

			if ( is_wp_error( $user_id ) ) {
				throw new \Exception( 'Failed to create user.' );
			}

			$result = \wp_set_object_terms( $user_id, $term_id, 'wnftd_public_address' );

			if ( is_wp_error( $result ) ) {
				throw new \Exception( 'Failed to create user.' );
			}

			return $user_id;
		} catch ( \Exception $e ) {
			\WNFTD\clean_up_term( $term, 'wnftd_public_address' );
			\WNFTD\clean_up_user( $user_id );
			throw $e;
		}
	}

	public function unassign_public_address_from_all_users( $public_address ) {
		$public_address = strtolower( $public_address );

		$term = $this->get_public_address_term( $public_address );

		if ( empty( $term ) ) {
			return;
		}

		$users = \get_objects_in_term( $term->term_id, 'wnftd_public_address' );

		foreach ( $users as $user ) {
			$this->unassign_public_address_from_user( $public_address, $user );
		}
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
