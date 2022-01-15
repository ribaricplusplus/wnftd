<?php
defined( 'ABSPATH' ) || exit;

global $post;

$nft = empty( $post ) || empty( $post->ID ) ? \WNFTD\Factory::create_nft() : \WNFTD\Factory::create_nft( $post->ID );

\wp_nonce_field( 'wnftd_save_nonce', 'wnftd_meta_nonce' );

?>
<div class="panel woocommerce_options_panel">
	<?php
		\woocommerce_wp_text_input(
			array(
				'id'    => 'token_id',
				'value' => $nft->get_token_id(),
				'label' => __( 'Token ID', 'wnftd' ),
				'type'  => 'text',
			)
		);
		?>

	<?php
		\woocommerce_wp_text_input(
			array(
				'id'    => 'contract_address',
				'value' => $nft->get_contract_address(),
				'label' => __( 'Contract address', 'wnftd' ),
				'type'  => 'text',
			)
		);
		?>

	<?php
		\woocommerce_wp_select(
			array(
				'id'      => 'contract_type',
				'value'   => $nft->get_contract_type(),
				'label'   => __( 'Contract type', 'wnftd' ),
				'options' => array(
					'erc721'  => 'ERC721',
					'erc1155' => 'ERC1155',
				),
			)
		);
		?>

	<?php
		\woocommerce_wp_text_input(
			array(
				'id'    => 'buy_url',
				'value' => $nft->get_buy_url(),
				'label' => __( 'URL', 'wnftd' ),
				'type'  => 'text',
			)
		);
		?>

	<?php
		\woocommerce_wp_text_input(
			array(
				'id'          => 'fake_owner',
				'value'       => $nft->get_fake_owner(),
				'label'       => __( 'Fake owner', 'wnftd' ),
				'desc_tip'    => true,
				'description' => __( 'This field is useful for testing. The public address set here will be treated as the NFT owner. Remember to delete this field later.', 'wnftd' ),
				'type'        => 'text',
			)
		);
		?>

</div>
