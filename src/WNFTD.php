<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

use Ethereum\Ethereum;

/**
 * Main plugin file.
 */
class WNFTD {

	public $auth;

	public $ethereum;

	public function __construct() {
		$rpc_api_key = \get_option( 'wnftd_rpc_api_key' );
		if ( $rpc_api_key ) {
			$this->ethereum = new Ethereum( $rpc_api_key );
		} else {
			$this->ethereum = new Ethereum();
		}
		$this->auth = new Authentication();
		$this->auth->init();
		( new Scripts_Loader() )->init();

		add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ) );

		\add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		\add_action( 'init', array( $this, 'register_post_types_and_taxonomies' ) );
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
