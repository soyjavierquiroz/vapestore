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

		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Company links', 'vapestore' ); ?>">
			<h2><?php esc_html_e( 'Company', 'vapestore' ); ?></h2>
			<ul class="menu footer-menu">
				<?php
				foreach (
					array(
						'about-us'   => __( 'About Us', 'vapestore' ),
						'contact-us' => __( 'Contact Us', 'vapestore' ),
						'faq'        => __( 'FAQ', 'vapestore' ),
					) as $page_slug => $page_label
				) :
					$page = get_page_by_path( $page_slug );

					if ( ! $page instanceof WP_Post ) {
						continue;
					}
					?>
					<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $page_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Customer care links', 'vapestore' ); ?>">
			<h2><?php esc_html_e( 'Customer Care', 'vapestore' ); ?></h2>
			<ul class="menu footer-menu">
				<?php
				foreach (
					array(
						'shipping-returns' => __( 'Shipping & Returns', 'vapestore' ),
						'privacy-policy'   => __( 'Privacy Policy', 'vapestore' ),
						'terms-conditions' => __( 'Terms & Conditions', 'vapestore' ),
						'age-policy'       => __( 'Age Policy', 'vapestore' ),
					) as $page_slug => $page_label
				) :
					$page = get_page_by_path( $page_slug );

					if ( ! $page instanceof WP_Post ) {
						continue;
					}
					?>
					<li><a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $page_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

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
