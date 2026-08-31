<?php
/**
 * Bulk variation ordering for WooCommerce variable products.
 *
 * @package VapeStoreCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VAPESTORE_BULK_VARIATION_ACTION = 'vapestore_bulk_variation_order';
const VAPESTORE_BULK_VARIATION_NONCE  = 'vapestore_bulk_variation_nonce';

/**
 * Check whether the bulk variation ordering feature is loaded.
 *
 * @return bool
 */
function vapestore_bulk_variation_is_loaded() {
	return defined( 'VAPESTORE_BULK_VARIATION_ACTION' )
		&& defined( 'VAPESTORE_BULK_VARIATION_NONCE' )
		&& function_exists( 'wc_get_product' );
}

/**
 * Check whether a product is eligible for bulk variation ordering.
 *
 * @param WC_Product|false|null $product Product object.
 * @return bool
 */
function vapestore_bulk_variation_is_eligible_product( $product ) {
	if ( ! vapestore_bulk_variation_is_loaded() || ! $product instanceof WC_Product_Variable ) {
		return false;
	}

	$available_variations = $product->get_available_variations();

	if ( empty( $available_variations ) ) {
		return false;
	}

	foreach ( $available_variations as $available_variation ) {
		$variation_id = isset( $available_variation['variation_id'] ) ? (int) $available_variation['variation_id'] : 0;

		if ( $variation_id > 0 && wc_get_product( $variation_id ) instanceof WC_Product_Variation ) {
			return true;
		}
	}

	return false;
}

/**
 * Build a readable label for a variation from its native attributes.
 *
 * @param WC_Product_Variation $variation Variation product.
 * @param WC_Product           $parent    Parent product.
 * @return string
 */
function vapestore_bulk_variation_get_label( $variation, $parent ) {
	$labels     = array();
	$attributes = $variation->get_variation_attributes();

	foreach ( $attributes as $attribute_key => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}

		$attribute_name  = preg_replace( '/^attribute_/', '', $attribute_key );
		$attribute_label = wc_attribute_label( $attribute_name, $parent );
		$value_label     = $value;

		if ( taxonomy_exists( $attribute_name ) ) {
			$term = get_term_by( 'slug', $value, $attribute_name );

			if ( $term instanceof WP_Term ) {
				$value_label = $term->name;
			}
		}

		$labels[] = sprintf(
			/* translators: 1: variation attribute label, 2: variation attribute value. */
			__( '%1$s: %2$s', 'vapestore-core' ),
			$attribute_label,
			$value_label
		);
	}

	if ( empty( $labels ) ) {
		return $variation->get_name();
	}

	if ( 1 === count( $labels ) ) {
		$parts = explode( ': ', $labels[0], 2 );

		return isset( $parts[1] ) ? $parts[1] : $labels[0];
	}

	return implode( ' / ', $labels );
}

/**
 * Render the bulk variation order table on single variable product pages.
 *
 * @return void
 */
function vapestore_bulk_variation_render_table() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! vapestore_bulk_variation_is_eligible_product( $product ) ) {
		return;
	}

	$available_variations = $product->get_available_variations();
	$product_id           = $product->get_id();
	?>
	<section class="vapestore-bulk-variations" aria-labelledby="vapestore-bulk-variations-title">
		<h2 id="vapestore-bulk-variations-title"><?php esc_html_e( 'Order Multiple Variations', 'vapestore-core' ); ?></h2>
		<p><?php esc_html_e( 'Enter quantities for the variations you want, then add them together to your cart.', 'vapestore-core' ); ?></p>

		<form class="vapestore-bulk-variations__form" method="post" action="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
			<?php wp_nonce_field( VAPESTORE_BULK_VARIATION_ACTION, VAPESTORE_BULK_VARIATION_NONCE ); ?>
			<input type="hidden" name="vapestore_bulk_variation_product_id" value="<?php echo esc_attr( $product_id ); ?>">
			<input type="hidden" name="vapestore_bulk_variation_submit" value="1">

			<div class="vapestore-bulk-variations__table-wrap">
				<table class="vapestore-bulk-variations__table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Variation', 'vapestore-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Stock', 'vapestore-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Price', 'vapestore-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Quantity', 'vapestore-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $available_variations as $available_variation ) :
							$variation_id = isset( $available_variation['variation_id'] ) ? (int) $available_variation['variation_id'] : 0;
							$variation    = $variation_id ? wc_get_product( $variation_id ) : false;

							if ( ! $variation instanceof WC_Product_Variation ) {
								continue;
							}

							$is_in_stock   = $variation->is_in_stock();
							$is_purchasable = $variation->is_purchasable();
							$can_order      = $is_in_stock && $is_purchasable;
							$stock_quantity = $variation->get_stock_quantity();
							$max_quantity   = $variation->get_max_purchase_quantity();
							$input_id       = 'vapestore-variation-qty-' . $variation_id;

							if ( ! $is_in_stock ) {
								$stock_text  = __( 'Out of stock', 'vapestore-core' );
								$stock_class = 'is-out-of-stock';
							} elseif ( $variation->managing_stock() && null !== $stock_quantity ) {
								$stock_quantity = max( 0, (int) $stock_quantity );
								$stock_display  = function_exists( 'wc_format_stock_quantity_for_display' )
									? wc_format_stock_quantity_for_display( $stock_quantity, $variation )
									: number_format_i18n( $stock_quantity );
								$stock_text  = sprintf(
									/* translators: %s: stock quantity. */
									_n( '%s in stock', '%s in stock', $stock_quantity, 'vapestore-core' ),
									$stock_display
								);
								$stock_class = 'is-in-stock';
							} else {
								$stock_text  = __( 'In stock', 'vapestore-core' );
								$stock_class = 'is-in-stock';
							}
							?>
							<tr>
								<th scope="row"><?php echo esc_html( vapestore_bulk_variation_get_label( $variation, $product ) ); ?></th>
								<td><span class="vapestore-bulk-variations__stock <?php echo esc_attr( $stock_class ); ?>"><?php echo esc_html( $stock_text ); ?></span></td>
								<td class="vapestore-bulk-variations__price"><?php echo wp_kses_post( $variation->get_price_html() ); ?></td>
								<td>
									<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
										<?php
										printf(
											/* translators: %s: variation label. */
											esc_html__( 'Quantity for %s', 'vapestore-core' ),
											esc_html( vapestore_bulk_variation_get_label( $variation, $product ) )
										);
										?>
									</label>
									<input
										id="<?php echo esc_attr( $input_id ); ?>"
										class="vapestore-bulk-variations__qty"
										type="number"
										name="vapestore_variation_qty[<?php echo esc_attr( $variation_id ); ?>]"
										value="0"
										min="0"
										<?php echo $max_quantity > 0 ? 'max="' . esc_attr( $max_quantity ) . '"' : ''; ?>
										step="1"
										inputmode="numeric"
										<?php disabled( ! $can_order ); ?>
									>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<button type="submit" class="vapestore-bulk-variations__submit"><?php esc_html_e( 'Add selected to cart', 'vapestore-core' ); ?></button>
		</form>
	</section>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'vapestore_bulk_variation_render_table', 35 );

/**
 * Use the bulk table instead of the native variable add-to-cart form when available.
 *
 * @return void
 */
function vapestore_bulk_variation_maybe_remove_native_form() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product instanceof WC_Product && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
	}

	if ( vapestore_bulk_variation_is_eligible_product( $product ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}
}
add_action( 'woocommerce_before_single_product', 'vapestore_bulk_variation_maybe_remove_native_form', 1 );

/**
 * Handle bulk variation add-to-cart submissions.
 *
 * @return void
 */
function vapestore_bulk_variation_handle_post() {
	if ( empty( $_POST['vapestore_bulk_variation_submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if (
		empty( $_POST[ VAPESTORE_BULK_VARIATION_NONCE ] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ VAPESTORE_BULK_VARIATION_NONCE ] ) ), VAPESTORE_BULK_VARIATION_ACTION )
	) {
		wc_add_notice( __( 'Unable to verify the bulk order request. Please try again.', 'vapestore-core' ), 'error' );
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wc_add_notice( __( 'Cart is not available right now. Please try again.', 'vapestore-core' ), 'error' );
		return;
	}

	$product_id = isset( $_POST['vapestore_bulk_variation_product_id'] ) ? absint( wp_unslash( $_POST['vapestore_bulk_variation_product_id'] ) ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : false;

	if ( ! $product instanceof WC_Product_Variable ) {
		wc_add_notice( __( 'Please choose a valid variable product.', 'vapestore-core' ), 'error' );
		return;
	}

	$posted_quantities = isset( $_POST['vapestore_variation_qty'] ) && is_array( $_POST['vapestore_variation_qty'] )
		? wp_unslash( $_POST['vapestore_variation_qty'] )
		: array();

	$added_count = 0;

	foreach ( $posted_quantities as $variation_id_raw => $quantity_raw ) {
		$variation_id = absint( $variation_id_raw );
		$quantity     = wc_stock_amount( absint( $quantity_raw ) );

		if ( $variation_id <= 0 || $quantity <= 0 ) {
			continue;
		}

		$variation = wc_get_product( $variation_id );

		if ( ! $variation instanceof WC_Product_Variation || (int) $variation->get_parent_id() !== $product_id ) {
			wc_add_notice( __( 'One selected variation could not be added to your cart.', 'vapestore-core' ), 'error' );
			continue;
		}

		if ( ! $variation->is_purchasable() || ! $variation->is_in_stock() ) {
			wc_add_notice(
				sprintf(
					/* translators: %s: variation label. */
					__( '%s is not available for purchase.', 'vapestore-core' ),
					vapestore_bulk_variation_get_label( $variation, $product )
				),
				'error'
			);
			continue;
		}

		$max_quantity = $variation->get_max_purchase_quantity();

		if ( $max_quantity > 0 && $quantity > $max_quantity ) {
			$quantity = wc_stock_amount( $max_quantity );
		}

		$passed_validation = apply_filters(
			'woocommerce_add_to_cart_validation',
			true,
			$product_id,
			$quantity,
			$variation_id,
			$variation->get_variation_attributes()
		);

		if ( ! $passed_validation ) {
			continue;
		}

		$added = WC()->cart->add_to_cart(
			$product_id,
			$quantity,
			$variation_id,
			$variation->get_variation_attributes()
		);

		if ( $added ) {
			$added_count++;
		} else {
			wc_add_notice(
				sprintf(
					/* translators: %s: variation label. */
					__( '%s could not be added to your cart.', 'vapestore-core' ),
					vapestore_bulk_variation_get_label( $variation, $product )
				),
				'error'
			);
		}
	}

	if ( $added_count > 0 ) {
		wc_add_notice(
			sprintf(
				/* translators: %d: number of variation lines added. */
				_n( '%d variation added to your cart.', '%d variations added to your cart.', $added_count, 'vapestore-core' ),
				$added_count
			),
			'success'
		);
	} else {
		wc_add_notice( __( 'Enter a quantity for at least one available variation.', 'vapestore-core' ), 'error' );
	}

	wp_safe_redirect( get_permalink( $product_id ) );
	exit;
}
add_action( 'wp_loaded', 'vapestore_bulk_variation_handle_post', 20 );
