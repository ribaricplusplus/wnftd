<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin file.
 */
class WNFTD {

	public $auth;

	public $ethereum;

	public $admin;

	public $product_controller;

	/**
	 * @throws \Exception
	 */
	public function __construct() {
		$this->admin_notices = new Admin\Notices();
		$this->admin_notices->init();

		$missing_dependencies = $this->get_missing_dependencies();

		if ( ! empty( $missing_dependencies ) ) {
			$names = array_values( $missing_dependencies );
			$names = implode( ', ', $names );
			$this->fail_initialization( sprintf( __( 'Missing plugins: %s', 'wnftd' ), $names ), 'fail_missing_plugins' );
		}

		if ( \is_admin() ) {
			$this->init_admin();
		}

		$this->product_controller = new Product_Controller();

		try {
			$this->ethereum = Factory::create_ethereum();
		} catch ( \Exception $e ) {
			$this->fail_initialization( __( 'Failed to initialize Ethereum RPC', 'wnftd' ), 'fail_ethereum' );
		}

		$this->auth = new Authentication();
		$this->auth->init();
		( new Scripts_Loader() )->init();

		\add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ) );

		\add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		\add_action( 'init', array( $this, 'register_post_types_and_taxonomies' ) );
	}

	public function init_admin() {
		$this->admin = new Admin(
			$this->admin_notices,
			new Admin\Meta_Boxes(
				new Admin\Meta_Boxes\NFT(),
				new Admin\Meta_Boxes\Product()
			),
			new Admin\Scripts(),
		);
		$this->admin->init();
	}

	public function get_missing_dependencies() {
		if ( ! \function_exists( '\\is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$missing = array();

		if ( ( \defined( '\\WNFTD_TEST' ) && \WNFTD_TEST ) ) {
			// We assume that all dependencies exist in phpunit tests (does not apply to e2e).
			return $missing;
		}

		if ( ! \is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$missing['woocommerce'] = 'WooCommerce';
		}

		return $missing;
	}

	/**
	 * @throws Initialization_Exception
	 */
	public function fail_initialization( $message = '', $name = 'initialization_failed' ) {
		if ( empty( $message ) ) {
			$message = __( 'Failed to initialize WooCommerce NFT Downloads.', 'wnftd' );
		}

		\trigger_error(
			$message,
			\E_USER_NOTICE
		);

		$this->admin_notices->add_notice( $name, $message );

		throw new Initialization_Exception();
	}

	public function register_data_stores( $stores ) {
		$stores['wnftd-nft'] = __NAMESPACE__ . '\\Data_Stores\\NFT';
		return $stores;
	}

	public function register_post_types_and_taxonomies() {
		\register_taxonomy(
			'wnftd_public_address',
			'user',
			array(
				'public'       => false,
				'hierarchical' => false,
				'show_in_rest' => true,
			)
		);

		\register_post_type(
			'wnftd-nft',
			array(
				'labels'              => array(
					'name'                  => __( 'NFTs', 'wnftd' ),
					'singular_name'         => __( 'NFT', 'wnftd' ),
					'all_items'             => __( 'All NFTs', 'wnftd' ),
					'menu_name'             => _x( 'NFTs', 'Admin menu name', 'wnftd' ),
					'add_new'               => __( 'Add New', 'wnftd' ),
					'add_new_item'          => __( 'Add new NFT', 'wnftd' ),
					'edit'                  => __( 'Edit', 'wnftd' ),
					'edit_item'             => __( 'Edit NFT', 'wnftd' ),
					'new_item'              => __( 'New NFT', 'wnftd' ),
					'view_item'             => __( 'View NFT', 'wnftd' ),
					'view_items'            => __( 'View NFTs', 'wnftd' ),
					'search_items'          => __( 'Search NFTs', 'wnftd' ),
					'not_found'             => __( 'No NFTs found', 'wnftd' ),
					'not_found_in_trash'    => __( 'No NFTs found in trash', 'wnftd' ),
					'parent'                => __( 'Parent NFT', 'wnftd' ),
					'featured_image'        => __( 'NFT image', 'wnftd' ),
					'set_featured_image'    => __( 'Set NFT image', 'wnftd' ),
					'remove_featured_image' => __( 'Remove NFT image', 'wnftd' ),
					'use_featured_image'    => __( 'Use as NFT image', 'wnftd' ),
					'insert_into_item'      => __( 'Insert into NFT', 'wnftd' ),
					'uploaded_to_this_item' => __( 'Uploaded to this NFT', 'wnftd' ),
					'filter_items_list'     => __( 'Filter NFTs', 'wnftd' ),
					'items_list_navigation' => __( 'NFTs navigation', 'wnftd' ),
					'items_list'            => __( 'NFTs list', 'wnftd' ),
					'item_link'             => __( 'NFT Link', 'wnftd' ),
					'item_link_description' => __( 'A link to an NFT.', 'wnftd' ),
				),
				'description'         => __( 'Non-Fungible Tokens', 'wnftd' ),
				'public'              => true,
				'show_ui'             => true,
				'map_meta_cap'        => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'hierarchical'        => false,
				'has_archive'         => false,
				'show_in_rest'        => true,
				'show_in_nav_menus'   => false,
				'supports'            => array( 'title', 'thumbnail' ),
			)
		);
	}

	public function rest_api_init() {
		$controllers = array(
			'Authentication',
		);
		foreach ( $controllers as $controller ) {
			$class    = __NAMESPACE__ . '\\REST\\' . $controller;
			$instance = new $class();
			$instance->register_routes();
		}
	}

}
