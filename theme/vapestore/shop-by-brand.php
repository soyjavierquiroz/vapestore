<?php
/**
 * Virtual Shop by Brand directory template.
 *
 * @package VapeStore
 */

get_header();

$brands = vapestore_get_product_brand_terms();
?>

<main class="site-main brand-directory">
	<div class="container">
		<header class="brand-directory__header">
			<h1><?php esc_html_e( 'Shop by Brand', 'vapestore' ); ?></h1>
			<p><?php esc_html_e( 'Browse products by brand.', 'vapestore' ); ?></p>
		</header>

		<?php if ( ! empty( $brands ) ) : ?>
			<?php vapestore_render_brand_grid( $brands ); ?>
		<?php else : ?>
			<p class="brand-directory__empty"><?php esc_html_e( 'Brands will appear here once products are assigned to them.', 'vapestore' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
