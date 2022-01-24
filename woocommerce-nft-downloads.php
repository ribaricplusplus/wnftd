<?php
/**
 * Plugin Name: WooCommerce NFT Downloads
 * Description: Makes it possible to download WooCommmerce products by owning a certain NFT.
 * Requires at least: 5.8
 * Requires PHP: 7.3
 * Version: 1.1.9
 * Author: Ribarich
 * Author URI: https://ribarich.me/
 * Text Domain: wnftd
 */

define( 'WNFTD_FILE', __FILE__ );

require 'vendor/autoload.php';
require 'src/functions.php';


try {
	\WNFTD\instance();
} catch ( \WNFTD\Initialization_Exception $e ) {

	if ( \WNFTD\is_testing() ) {
		throw $e;
	}
}
