<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class Scripts_Loader implements Interfaces\Initializable {

	public $product_controller;

	public $auth;

	public function init() {
		\add_action( 'init', array( $this, 'register_scripts' ) );
	}

	public function __construct( $product_controller, $auth ) {
		$this->product_controller = $product_controller;
		$this->auth               = $auth;
	}

	public function register_scripts() {
		$data = $this->get_script_data( 'verify-nft-ownership' );

		\wp_register_script(
			'wnftd-verify-nft-ownership',
			\plugins_url( 'build/verify-nft-ownership.js', \WNFTD_FILE ),
			$data['dependencies'],
			$data['version'],
			true
		);
	}

	/**
	 * Get script data as produced by dependency extraction webpack plugin
	 *
	 * @param string $script_name Script name defined by a webpack entry point.
	 * @return array Script data (version, dependencies)
	 */
	protected function get_script_data( $script_name ) {
		$assets_path = plugin_dir_path( \WNFTD_FILE ) . 'build/' . $script_name . '.asset.php';

		if ( file_exists( $assets_path ) ) {
			$data = require $assets_path;
			return $data;
		}

		return array(
			'dependencies' => array(),
			'version'      => '',
		);
	}

	public function add_inline_data( $script ) {
		global $product;

		switch ( $script ) {
			case 'wnftd-verify-nft-ownership':
				if ( empty( $product ) ) {
					break;
				}
				$auth_nonce = \wp_create_nonce( 'wnftd_auth' );

				$valid_networks = \WNFTD\get_valid_networks();
				$required_nfts  = $this->product_controller->get_required_nfts_ids( $product );
				$required_nfts  = array_map( array( '\\WNFTD\\Factory', 'create_nft' ), $required_nfts );
				$required_nfts  = array_values(
					array_map(
						function( $nft ) {
							return array(
								'name'    => $nft->get_name(),
								'image'   => $nft->get_image_id() ? \wp_get_attachment_image_url( $nft->get_image_id() ) : '',
								'buyUrl'  => $nft->get_buy_url(),
								'network' => $nft->get_network(),
							);
						},
						$required_nfts
					)
				);
				if ( empty( $required_nfts ) ) {
					$chain_id = 0;
				} else {
					$nft      = $required_nfts[0];
					$network  = $valid_networks[ $nft['network'] ];
					$chain_id = $network['chain_id'];
				}

				$data = array(
					'userLoggedIn'             => \is_user_logged_in(),
					'nonces'                   => array(
						'auth'     => $auth_nonce,
						'download' => \wp_create_nonce( 'wnftd_product_download' ),
						'_wpnonce' => \wp_create_nonce( 'wp_rest' ),
					),
					'nonceActions'             => array(
						'wnftd_auth'             => 'auth',
						'wnftd_product_download' => 'download',
						'wp_rest'                => '_wpnonce',
					),
					'userOwnedPublicAddresses' => $this->auth->get_public_addresses( \get_current_user_id() ),
					'messageForSigning'        => \WNFTD\get_auth_message( $auth_nonce ),
					'productId'                => $product->get_id(),
					'requiredNfts'             => $required_nfts,
					'downloadsUrl'             => \WNFTD\get_downloads_page_permalink(),
					'chainId'                  => $chain_id,
				);
				$data = \wp_json_encode( $data );

				$js = <<<JS
if ( window.wnftdData === undefined ) window.wnftdData = {};
window.wnftdData = $data;
JS;
				\wp_add_inline_script(
					$script,
					$js,
					'before'
				);
				break;
		}
	}

}
