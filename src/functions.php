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
		__( 'Sign this message to authenticate with the application. Here is a nonce for security: %s', 'wnftd' ),
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

function get_api_key( $force = false ) {
	static $api_key;

	if ( ! empty( $api_key ) && ! $force ) {
		return $api_key;
	}

	$api_key = \get_option( 'wnftd_rpc_api_key' );

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

	$api_key = null;
	return $api_key;
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
