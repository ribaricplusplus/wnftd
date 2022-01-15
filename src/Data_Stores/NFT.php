<?php

namespace WNFTD\Data_Stores;

defined( 'ABSPATH' ) || exit;

class NFT extends \WC_Data_Store_WP implements \WC_Object_Data_Store_Interface {

	protected $internal_meta_keys = array(
		'token_id',
		'contract_address',
		'contract_type',
		'fake_owner',
		'_thumbnail_id',
		'buy_url',
	);

	protected $meta_type = 'post';

	/**
	 * Method to create a new record of a WC_Data based object.
	 *
	 * @param \WNFTD\NFT $data Data object.
	 */
	public function create( &$data ) {
		$id = \wp_insert_post(
			array(
				'post_type'   => 'wnftd-nft',
				'post_title'  => empty( $data->get_name() ) ? 'NFT' : $data->get_name(),
				'post_status' => $data->get_status() ? $data->get_status() : 'publish',
				'post_author' => \get_current_user_id(),
			),
			true,
			false
		);

		if ( \is_wp_error( $id ) ) {
			throw new \Exception( 'Failed to insert post.' );
		}

		$data->set_id( $id );
		$this->update_post_meta( $data, true );
		$data->save_meta_data();
		$data->apply_changes();
	}

	/**
	 * Method to read a record. Creates a new WC_Data based object.
	 *
	 * @param \WNFTD\NFT $data Data object.
	 */
	public function read( &$data ) {
		$post = \get_post( $data->get_id() );

		if ( empty( $post ) || $post->post_type !== 'wnftd-nft' ) {
			throw new \Exception( 'Failed reading NFT.' );
		}

		$token_id         = \get_post_meta( $post->ID, 'token_id', true );
		$contract_address = \get_post_meta( $post->ID, 'contract_address', true );
		$contract_type    = \get_post_meta( $post->ID, 'contract_type', true );
		$fake_owner       = \get_post_meta( $post->ID, 'fake_owner', true );
		$buy_url          = \get_post_meta( $post->ID, 'buy_url', true );

		$data->set_props(
			array(
				'name'             => $post->post_title,
				'token_id'         => empty( $token_id ) ? '' : $token_id,
				'contract_address' => empty( $contract_address ) ? '' : $contract_address,
				'contract_type'    => empty( $contract_type ) ? '' : $contract_type,
				'status'           => $post->post_status,
				'fake_owner'       => $fake_owner,
				'image_id'         => \get_post_thumbnail_id( $post->ID ),
				'buy_url'          => $buy_url,
			)
		);

		$data->set_object_read();

	}

	/**
	 * Updates a record in the database.
	 *
	 * @param \WNFTD\NFT $data Data object.
	 */
	public function update( &$data ) {
		if ( doing_action( 'save_post' ) ) {
			throw new \Exception( 'Infinite loop.' );
		}

		$data->save_meta_data();
		$changes = $data->get_changes();

		if ( array_intersect(
			array(
				'name',
				'status',
				'image_id',
			),
			array_keys( $changes )
		) ) {
			$id = \wp_update_post(
				array(
					'ID'          => $data->get_id(),
					'post_title'  => empty( $data->get_name() ) ? 'NFT' : $data->get_name(),
					'post_status' => $data->get_status() ? $data->post_status : 'publish',
				),
				true,
				false
			);

			if ( \is_wp_error( $id ) ) {
				throw new \Exception( 'Failed updating NFT.' );
			}

			\set_post_thumbnail( $data->get_id(), $data->get_image_id() );
		}

		$this->update_post_meta( $data );
		$data->apply_changes();
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @param  \WNFTD\NFT $data Data object.
	 * @param  array      $args Array of args to pass to the delete method.
	 * @return bool result
	 */
	public function delete( &$data, $args = array() ) {
		if ( empty( $data->get_id() ) ) {
			return;
		}

		$deleted = \wp_delete_post( $data->get_id(), true );

		if ( empty( $deleted ) ) {
			throw new \Exception( 'Failed to delete NFT.' );
		}

		$data->set_id( 0 );
	}

	protected function update_post_meta( &$data, $force = false ) {
		$meta_key_to_props = array(
			'token_id'         => 'token_id',
			'contract_address' => 'contract_address',
			'contract_type'    => 'contract_type',
			'fake_owner'       => 'fake_owner',
			'buy_url'          => 'buy_url',
		);

		$props_to_update = $force ? $meta_key_to_props : $this->get_props_to_update( $data, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $data->{"get_$prop"}( 'edit' );
			$this->update_or_delete_post_meta( $data, $meta_key, $value );
		}
	}

}
