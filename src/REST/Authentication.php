<?php

namespace WNFTD\REST;

defined( 'ABSPATH' ) || exit;

use WNFTD\Factory;

class Authentication extends \WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'wnftd/v1';
		$this->rest_base = 'auth';
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'authenticate' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_authentication_args(),
				),
			)
		);
	}

	/**
	 * Authenticates user
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function authenticate( $request ) {
		try {
			if ( ! wp_verify_nonce( $request['wnftd_auth_nonce'], 'wnftd_auth' ) ) {
				return Factory::create_rest_error( 'invalid_nonce' );
			}

			$is_address_valid = \WNFTD\auth()->verify_public_address( $request['public_address'], \WNFTD\get_auth_message( $request['wnftd_auth_nonce'] ), $request['signature'] );

			if ( ! $is_address_valid ) {
				return new \WP_Error(
					'wnftd_rest_error',
					'Bad public address.',
					array( 'status' => 401 )
				);
			}

			$user = \WNFTD\auth()->get_user_by_public_address( $request['public_address'] );
			if ( ! empty( $user ) ) {
				$user = \get_user( $user );
			}

			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if ( ! $user ) {
					// No user is assigned this public address. Assign to current user.
					\WNFTD\auth()->assign_public_address_to_user( $request['public_address'], $current_user->ID );
					return rest_ensure_response(
						array(
							'code'    => 'added_public_address',
							'message' => __( 'Public address added for current user.', 'wnftd' ),
						)
					);
				} elseif ( $user->ID === $current_user->ID ) {
					return rest_ensure_response(
						array(
							'code'    => 'already_authenticated',
							'message' => __( 'Already authenticated.', 'wnftd' ),
						)
					);
				} else {
					// Transfer ownership of public address to new user.
					\WNFTD\auth()->transfer_address_ownership( $user->ID, $current_user->ID, $request['public_address'] );
					return rest_ensure_response(
						array(
							'code'    => 'ownership_transferred',
							'message' => __( 'Public address ownership transferred.', 'wnftd' ),
						)
					);
				}
			} else {
				if ( $user ) {
					\WNFTD\auth()->log_in_user( $user );
					return rest_ensure_response(
						array(
							'code'    => 'logged_in',
							'message' => __( 'Logged in successfully.', 'wnftd' ),
						)
					);
				} else {
					try {
						$user_id = \WNFTD\auth()->create_new_user( $request['public_address'] );
						\WNFTD\auth()->log_in_user( $user_id );
						return rest_ensure_response(
							array(
								'code'    => 'new_user_created',
								'message' => __( 'Logged in successfully.', 'wnftd' ),
							)
						);
					} catch ( \Exception $e ) {
						return \WP_Error(
							'wnftd_rest_error',
							__( 'Failed to create user.', 'wnftd' ),
							array( 'status' => 500 )
						);
					}
				}
			}
		} catch ( \Exception $e ) {
			return Factory::create_rest_error();
		}
	}

	/**
	 * @return array
	 */
	public function get_authentication_args() {
		// Note: It would be a bad idea to pass a message as a parameter to the
		// REST API. The message must contain a nonce. Otherwise, it would be
		// possible to steal the signature for a certain message and send it to
		// the REST API.
		return array(
			'public_address'   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'required'          => true,
			),
			'signature'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'required'          => true,
			),
			'wnftd_auth_nonce' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_key',
				'required'          => true,
			),
		);
	}

}
