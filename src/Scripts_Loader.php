<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Scripts_Loader {

	public function __construct() {
		// Add scripts
	}

	/**
	 * Get script data as produced by dependency extraction webpack plugin
	 *
	 * @param string $script_name Script name defined by a webpack entry point.
	 * @return array Script data (version, dependencies)
	 */
	protected function get_script_data( $script_name ) {
		$assets_path = plugin_dir_path( RIBARICH_DDLB_FILE ) . 'build/' . $script_name . '.asset.php';
		if ( file_exists( $assets_path ) ) {
			$data = require $assets_path;
			return $data;
		}
	}

}
