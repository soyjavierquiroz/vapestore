<?php
/**
 * Theme footer.
 *
 * @package VapeStore
 */

?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<p class="site-footer__copyright">
			&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>

		<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer navigation', 'vapestore' ); ?>">
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
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
