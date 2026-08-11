<?php
/**
 * Theme footer.
 *
 * @package VapeStore
 */

?>
<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<h2><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
			<p><?php esc_html_e( 'A simple storefront for browsing products and placing orders.', 'vapestore' ); ?></p>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'vapestore' ); ?>">
				<h2><?php esc_html_e( 'Information', 'vapestore' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'menu footer-menu',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<nav class="site-footer__store" aria-label="<?php esc_attr_e( 'Store links', 'vapestore' ); ?>">
			<h2><?php esc_html_e( 'Store', 'vapestore' ); ?></h2>
			<ul class="menu footer-menu">
				<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'vapestore' ); ?></a></li>
				<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>"><?php esc_html_e( 'My Account', 'vapestore' ); ?></a></li>
			</ul>
		</nav>
	</div>

	<div class="container site-footer__bottom">
		<p>
			&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
