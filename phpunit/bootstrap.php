<?php
/**
 * PHPUnit bootstrap file
 */

// Require composer dependencies.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// If we're running in WP's build directory, ensure that WP knows that, too.
if ( 'build' === getenv( 'LOCAL_DIR' ) ) {
	define( 'WP_RUN_CORE_TESTS', true );
}

$_tests_dir = dirname( __DIR__ ) . '/wp-phpunit';

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Always load PayPal Standard for unit tests.
tests_add_filter( 'woocommerce_should_load_paypal_standard', '__return_true' );

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$plugins_dir = dirname( dirname( __DIR__ ) );
	// Load WC
	define( 'WC_TAX_ROUNDING_MODE', 'auto' );
	define( 'WC_USE_TRANSACTIONS', false );
	require $plugins_dir . '/woocommerce/woocommerce.php';

	// Load WNFTD
	require dirname( __DIR__ ) . '/woocommerce-nft-downloads.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Install WC. Copied from how WC does the installation in its own tests.
function _install_wc() {
	define( 'WP_UNINSTALL_PLUGIN', true );
	define( 'WC_REMOVE_ALL_DATA', true );
	\Automattic\WooCommerce\Admin\Install::create_tables();
	\Automattic\WooCommerce\Admin\Install::create_events();
	WC_Install::install();
	$GLOBALS['wp_roles'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	wp_roles();
	echo esc_html( 'Installing WooCommerce...' . PHP_EOL );
}
tests_add_filter( 'setup_theme', '_install_wc' );

/**
 * Adds a wp_die handler for use during tests.
 *
 * If bootstrap.php triggers wp_die, it will not cause the script to fail. This
 * means that tests will look like they passed even though they should have
 * failed. So we throw an exception if WordPress dies during test setup. This
 * way the failure is observable.
 *
 * @param string|WP_Error $message The error message.
 *
 * @throws Exception When a `wp_die()` occurs.
 */
function fail_if_died( $message ) {
	if ( is_wp_error( $message ) ) {
		$message = $message->get_error_message();
	}

	throw new Exception( 'WordPress died: ' . $message );
}
tests_add_filter( 'wp_die_handler', 'fail_if_died' );

// Enable the widget block editor.
tests_add_filter( 'gutenberg_use_widgets_block_editor', '__return_true' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Use existing behavior for wp_die during actual test execution.
remove_filter( 'wp_die_handler', 'fail_if_died' );
