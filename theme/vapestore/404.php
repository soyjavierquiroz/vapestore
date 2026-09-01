<?php
/**
 * Template for 404 errors.
 *
 * @package VapeStore
 */

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<main id="primary" class="site-main">
	<div class="container">
		<section class="vapestore-empty-state vapestore-empty-state--404" aria-labelledby="vapestore-404-title">
			<p class="vapestore-empty-state__eyebrow"><?php esc_html_e( '404', 'vapestore' ); ?></p>
			<h1 id="vapestore-404-title"><?php esc_html_e( 'Page not found', 'vapestore' ); ?></h1>
			<p><?php esc_html_e( 'This page may have moved, or the link may no longer be available. You can head back to the shop or return home.', 'vapestore' ); ?></p>
			<div class="vapestore-empty-state__actions">
				<a class="button button--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Back to Shop', 'vapestore' ); ?></a>
				<a class="button button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'vapestore' ); ?></a>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
