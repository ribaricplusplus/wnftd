<?php

namespace WNFTD;

defined( 'ABSPATH' ) || exit;

class NFT extends \WC_Data {

	protected $plugin_id = 'wnftd';

	protected $cache_group = 'nft';

	protected $meta_type = 'post';

	protected $object_type = 'nft';

	protected $data = array(
		'token_id'         => '',
		'contract_address' => '',
		'contract_type'    => '',
		'name'             => '',
		'status'           => 'publish',
		'fake_owner' => ''
	);

	/*
	|--------------------------------------------------------------------------
	| Dependencies and constructor
	|--------------------------------------------------------------------------
	*/

	/**
	 * Warning: This class should usually not be instantiated directly. Use the
	 * corresponding Factory method.
	 *
	 * @param int|object|array $item ID to load from the DB, or NFT object.
	 */
	public function __construct( $item = 0 ) {
		parent::__construct( $item );
		if ( $item instanceof NFT ) {
			$this->set_id( $item->get_id() );
		} elseif ( is_numeric( $item ) && $item > 0 ) {
			$this->set_id( $item );
		} elseif ( $item <= 0 ) {
			$this->set_object_read( true );
		} else {
			throw new \InvalidArgumentException();
		}

		$this->data_store = \WC_Data_Store::load( 'wnftd-nft' );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	public function get_token_id( $context = 'view' ) {
		return $this->get_prop( 'token_id', $context );
	}

	public function get_contract_address( $context = 'view' ) {
		return $this->get_prop( 'contract_address', $context );
	}

	public function get_contract_type( $context = 'view' ) {
		return $this->get_prop( 'contract_type', $context );
	}

	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}

	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	public function get_fake_owner( $context = 'view' ) {
		return $this->get_prop( 'fake_owner', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	public function set_token_id( $data ) {
		return $this->set_prop( 'token_id', $data );
	}

	public function set_contract_address( $data ) {
		return $this->set_prop( 'contract_address', $data );
	}

	public function set_contract_type( $data ) {
		return $this->set_prop( 'contract_type', $data );
	}

	public function set_name( $data ) {
		return $this->set_prop( 'name', $data );
	}

	public function set_status( $data ) {
		return $this->set_prop( 'status', $data );
	}

	public function set_fake_owner( $data ) {
		return $this->set_prop( 'fake_owner', $data );
	}

	/*
	|--------------------------------------------------------------------------
	| Utilities
	|--------------------------------------------------------------------------
	*/

	public function is_owner( $public_address ) {
		$contract = Factory::create_nft_contract( $this );
		return $contract->is_owner( $public_address, $this );
	}

}
