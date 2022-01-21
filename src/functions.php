<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

function instance() {
	static $instance = null;

	if ( ! $instance ) {
		$instance = new WNFTD();
	}

	return $instance;
}

/**
 * @param string $nonce
 * @return string
 */
function get_auth_message( $nonce ) {
	return sprintf(
		__( 'Sign this message to authenticate with the application. Nonce: %s', 'wnftd' ),
		$nonce
	);
}

function auth() {
	return instance()->auth;
}

/**
 * @param string $path Example 'notices/error'.
 * @param array  $args Arguments to make available to view.
 */
function view( $path, $args = array() ) {
	require \plugin_dir_path( \WNFTD_FILE ) . 'views/' . $path . '.php';
}

function admin() {
	return instance()->admin;
}

/**
 * Keep only items in $arr where key is in $keys.
 */
function filter_keys( $arr, $keys ) {
	return array_filter(
		$arr,
		function( $v, $k ) use ( $keys ) {
			return \in_array( $k, $keys );
		},
		\ARRAY_FILTER_USE_BOTH
	);
}

/**
 * @throws \Exception
 */
function get_api_key( $network = 'ethereum' ) {
	$valid_networks = get_valid_networks();

	if ( ! is_valid_network( $network ) ) {
		throw new \InvalidArgumentException();
	}

	$key = $valid_networks[ $network ]['rpc_option'];

	$api_key = \get_option( $key );

	if ( $api_key ) {
		return $api_key;
	}

	if ( \file_exists( \plugin_dir_path( \WNFTD_FILE ) . 'secrets.php' ) ) {
		$secrets = require \plugin_dir_path( \WNFTD_FILE ) . 'secrets.php';
		if ( ! empty( $secrets['eth_api_url'] ) ) {
			$api_key = $secrets['eth_api_url'];
			return $api_key;
		}
	}

	$api_key = apply_filters( 'wnftd_api_key_fallback', null, $network );

	if ( $api_key ) {
		return $api_key;
	}

	throw new \Exception( 'RPC URL not found.' );

}

function is_testing() {
	return defined( 'WNFTD_TEST' ) && \WNFTD_TEST;
}

function clean_unslash( $var ) {
	return \wc_clean( \wp_unslash( $var ) );
}

function product_controller() {
	return instance()->product_controller;
}

function public_addresses_equal( $a, $b ) {
	return strtolower( $a ) === strtolower( $b );
}

function get_downloads_page_permalink() {
	return add_query_arg( 'downloads', 1, \wc_get_page_permalink( 'myaccount' ) );
}

function is_extension_loaded( $extension ) {
	$is_loaded = \extension_loaded( $extension );

	return \apply_filters(
		'wnftd_is_extension_loaded',
		$is_loaded,
		$extension
	);
}

function get_valid_networks() {
	return array(
		'ethereum' => array(
			'chain_id'   => 1,
			'rpc_option' => 'wnftd_rpc_api_key',
		),
		'polygon'  => array(
			'chain_id'   => 137,
			'rpc_option' => 'wnftd_rpc_api_key_polygon',
		),
	);
}

function is_valid_network( $network ) {
	return in_array( $network, array_keys( get_valid_networks() ) );
}

function is_removeable( $thing ) {
	return ! empty( $thing ) && ! \is_wp_error( $thing );
}

function clean_up_term( $term, $taxonomy ) {
	if ( ! is_removeable( $term ) ) {
		return;
	}

	if ( is_array( $term ) ) {
		return \wp_delete_term( $term['term_id'], $taxonomy );
	} elseif ( is_numeric( $term ) ) {
		return \wp_delete_term( $term, $taxonomy );
	} elseif ( is_a( $term, 'WP_Term' ) ) {
		return \wp_delete_term( $term->term_id, $taxonomy );
	}

	throw new \Exception();
}

function clean_up_user( $user ) {
	if ( ! is_removeable( $user ) ) {
		return;
	}

	if ( is_a( $user, 'WP_User' ) ) {
		return \wp_delete_user( $user->ID );
	} elseif ( is_numeric( $user ) ) {
		return \wp_delete_user( $user );
	}

	throw new \Exception();
}

/**
 * Proxy call function so that the implementation can be changed for testing.
 *
 * @param string $function
 * @param array $params
 * @return mixed
 */
function call( $function, $params = array() ) {
	$function = apply_filters( "wnftd_proxy_$function", $function, $params );
	return call_user_func_array( $function, $params );
}
