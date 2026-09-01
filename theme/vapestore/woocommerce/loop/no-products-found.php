<?php
/**
 * Product loop empty state.
 *
 * @package VapeStore
 */

defined( 'ABSPATH' ) || exit;

$shop_url       = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$is_search      = is_search();
$search_query   = get_search_query();
$copy           = $is_search ? __( 'Try another search or browse the shop.', 'vapestore' ) : __( 'There are no products in this section right now. Browse the full shop to keep looking.', 'vapestore' );
$heading_id     = wp_unique_id( 'vapestore-empty-products-title-' );
?>

<section class="vapestore-empty-state vapestore-empty-state--products" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'No products found', 'vapestore' ); ?></h2>
	<p><?php echo esc_html( $copy ); ?></p>

	<?php if ( $is_search && function_exists( 'get_product_search_form' ) ) : ?>
		<div class="vapestore-empty-state__search">
			<?php get_product_search_form(); ?>
		</div>
	<?php elseif ( $is_search && function_exists( 'get_search_form' ) ) : ?>
		<div class="vapestore-empty-state__search">
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>

	<div class="vapestore-empty-state__actions">
		<a class="button button--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Browse Shop', 'vapestore' ); ?></a>
	</div>
</section>
