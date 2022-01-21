<?php

namespace WNFTD\Test;

function mock_function( $original, $mock ) {
	add_filter(
		"wnftd_proxy_$original",
		function() use ( &$mock ) {
			return $mock;
		}
	);
}
