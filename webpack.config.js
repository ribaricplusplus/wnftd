const baseConfig = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = {
	...baseConfig,
	entry: {
		'verify-nft-ownership': './js/verify-nft-ownership/index.js',
	},
};
