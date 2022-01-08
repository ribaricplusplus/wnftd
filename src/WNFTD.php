<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin file.
 */
class WNFTD {

	public function __construct() {
		new Scripts_Loader();
	}

}
