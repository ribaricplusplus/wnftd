<?php

defined( 'ABSPATH' ) || exit;

global $product_object;

?>

<div id="wnftd_nft_product_data" class="panel woocommerce_options_panel">
	<p>
		<?php _e( 'Setting these options makes the product accessible only to the owners of specific NFTs.', 'wnftd' ); ?>
	</p>
	<p class="form-field">
		<label for="wnftd_product_nfts">
			<?php _e( 'Required NFTs for access', 'wnftd' ); ?>
		</label>
		<select style="width: 50%;" id="wnftd_product_nfts" name="wnftd_product_nfts[]" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any NFT', 'wnftd' ); ?>">
			<?php
				$nfts         = new \WP_Query(
					array(
						'post_type'      => 'wnftd-nft',
						'status'         => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);
				$meta         = $product_object->get_meta( 'wnftd_product_nft', false );
				$product_nfts = array_map(
					function( $meta ) {
						return $meta->value;
					},
					$meta
				);

				$nfts = array_map( array( '\\WNFTD\\Factory', 'create_nft' ), $nfts->posts );

				foreach ( $nfts as $nft ) {
					echo '<option value="' . $nft->get_id() . '" ' . wc_selected( $nft->get_id(), $product_nfts ) . '>' . esc_html( $nft->get_name() ) . '</option>';
				}
				?>
		</select>
	</p>
</div>
