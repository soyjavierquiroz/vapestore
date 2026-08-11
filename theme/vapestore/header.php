<?php
/**
 * Theme header.
 *
 * @package VapeStore
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="utility-bar">
		<div class="container utility-bar__inner">
			<div class="utility-bar__support">
				<?php esc_html_e( 'Customer Support', 'vapestore' ); ?>
			</div>
			<div class="utility-bar__links">
				<?php
				$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
				$orders_url  = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : $account_url;
				?>
				<a href="<?php echo esc_url( $orders_url ); ?>"><?php esc_html_e( 'Track Order', 'vapestore' ); ?></a>
				<a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'My Account', 'vapestore' ); ?></a>
			</div>
		</div>
	</div>

	<div class="container site-header__main">
		<div class="site-branding">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
				<?php
			}
			?>
		</div>

		<div class="product-search">
			<?php
			if ( function_exists( 'get_product_search_form' ) ) {
				get_product_search_form();
			} else {
				get_search_form();
			}
			?>
		</div>

		<div class="header-actions">
			<a class="header-action" href="<?php echo esc_url( $account_url ); ?>" aria-label="<?php esc_attr_e( 'My Account', 'vapestore' ); ?>">
				<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="22" height="22">
					<path d="M12 12c2.8 0 5-2.2 5-5s-2.2-5-5-5-5 2.2-5 5 2.2 5 5 5zm0 2c-3.3 0-9 1.7-9 5v3h18v-3c0-3.3-5.7-5-9-5z" fill="currentColor"/>
				</svg>
				<span><?php esc_html_e( 'Account', 'vapestore' ); ?></span>
			</a>

			<a class="header-action header-cart-link" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'vapestore' ); ?>">
				<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="22" height="22">
					<path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM7.2 14.8h8.9c.8 0 1.5-.4 1.8-1.1L21 7H6.2L5.5 4H2V2h5l1 4h14l-3.3 8.5c-.5 1.4-1.8 2.3-3.3 2.3H7.2v-2z" fill="currentColor"/>
				</svg>
				<span><?php esc_html_e( 'Cart', 'vapestore' ); ?></span>
				<span class="header-cart-count"><?php echo esc_html( function_exists( 'vapestore_get_cart_count' ) ? vapestore_get_cart_count() : 0 ); ?></span>
			</a>

			<button class="menu-toggle" type="button" aria-controls="site-primary-navigation" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'vapestore' ); ?></span>
				<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="24" height="24">
					<path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z" fill="currentColor"/>
				</svg>
			</button>
		</div>
	</div>

	<div class="container site-header__nav">
		<nav id="site-primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'vapestore' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu primary-menu',
						'fallback_cb'    => false,
						'depth'          => 2,
					)
				);
			} else {
				$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
				?>
				<ul class="menu primary-menu">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'vapestore' ); ?></a></li>
					<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop', 'vapestore' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</nav>
	</div>
</header>
