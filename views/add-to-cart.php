<?php

defined( 'ABSPATH' ) || exit;

try {
	\WNFTD\instance()->scripts_loader->add_inline_data( 'wnftd-verify-nft-ownership' );
} catch ( \Exception $e ) {
	return;
}

\wp_enqueue_script( 'wnftd-verify-nft-ownership' );

?>
<div class="wnftd-single-product-root"></div>
