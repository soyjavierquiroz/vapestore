<?php
/**
 * VapeStore theme functions.
 *
 * @package VapeStore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/inc/acf-home.php';

/**
 * Set up theme support and menus.
 */
function vapestore_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'vapestore' ),
			'footer'  => __( 'Footer Menu', 'vapestore' ),
		)
	);
}
add_action( 'after_setup_theme', 'vapestore_setup' );

/**
 * Enqueue theme assets.
 */
function vapestore_enqueue_assets() {
	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_path  = $theme_dir . '/assets/css/main.css';
	$js_path   = $theme_dir . '/assets/js/main.js';

	wp_enqueue_style(
		'vapestore-main',
		$theme_uri . '/assets/css/main.css',
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : '0.1.0'
	);

	wp_enqueue_script(
		'vapestore-main',
		$theme_uri . '/assets/js/main.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : '0.1.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'vapestore_enqueue_assets' );

/**
 * Show the first native WooCommerce product brand on product cards.
 */
function vapestore_loop_product_brand() {
	global $product;

	if ( ! $product instanceof WC_Product || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$brands = get_the_terms( $product->get_id(), 'product_brand' );

	if ( empty( $brands ) || is_wp_error( $brands ) ) {
		return;
	}

	$brand = reset( $brands );
	$link  = get_term_link( $brand, 'product_brand' );
	?>
	<div class="product-card-brand">
		<?php if ( ! is_wp_error( $link ) ) : ?>
			<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $brand->name ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $brand->name ); ?>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_shop_loop_item_title', 'vapestore_loop_product_brand', 9 );

/**
 * Register theme Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function vapestore_customize_register( $wp_customize ) {
	$wp_customize->add_setting(
		'vapestore_mobile_logo',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'vapestore_mobile_logo',
			array(
				'label'       => __( 'Mobile Logo', 'vapestore' ),
				'description' => __( 'Upload the compact isotipo logo for mobile headers.', 'vapestore' ),
				'section'     => 'title_tagline',
				'settings'    => 'vapestore_mobile_logo',
			)
		)
	);
}
add_action( 'customize_register', 'vapestore_customize_register' );

/**
 * Use simple theme wrappers for WooCommerce screens.
 */
function vapestore_woocommerce_wrapper_start() {
	?>
	<main class="store-main">
		<div class="container">
	<?php
}

/**
 * Close theme wrappers for WooCommerce screens.
 */
function vapestore_woocommerce_wrapper_end() {
	?>
		</div>
	</main>
	<?php
}

/**
 * Keep WooCommerce content inside the theme layout and remove the default sidebar.
 */
function vapestore_woocommerce_layout_hooks() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	add_action( 'woocommerce_before_main_content', 'vapestore_woocommerce_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'vapestore_woocommerce_wrapper_end', 10 );
}
add_action( 'wp', 'vapestore_woocommerce_layout_hooks' );

/**
 * Get the current WooCommerce cart item count.
 *
 * @return int
 */
function vapestore_get_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Add the header cart count to WooCommerce cart fragments.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function vapestore_cart_count_fragment( $fragments ) {
	ob_start();
	?>
	<span class="header-cart-count"><?php echo esc_html( vapestore_get_cart_count() ); ?></span>
	<?php
	$fragments['.header-cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'vapestore_cart_count_fragment' );
