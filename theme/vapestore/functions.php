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
	$product_id = function_exists( 'is_product' ) && is_product() ? get_queried_object_id() : 0;

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

	wp_add_inline_script(
		'vapestore-main',
		'window.vapestoreRecentlyViewed = ' . wp_json_encode(
			array(
				'endpoint'         => esc_url_raw( rest_url( 'vapestore/v1/recently-viewed' ) ),
				'currentProductId' => absint( $product_id ),
			)
		) . ';',
		'before'
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
 * Get the public Shop by Brand directory URL.
 *
 * @return string
 */
function vapestore_get_brand_directory_url() {
	return home_url( '/shop-by-brand/' );
}

/**
 * Get non-empty native WooCommerce product brand terms.
 *
 * @param array<string, mixed> $args Optional get_terms arguments.
 * @return WP_Term[]
 */
function vapestore_get_product_brand_terms( $args = array() ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return array();
	}

	$brands = get_terms(
		wp_parse_args(
			$args,
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		)
	);

	if ( is_wp_error( $brands ) || ! is_array( $brands ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$brands,
			static function ( $brand ) {
				return $brand instanceof WP_Term && $brand->count > 0;
			}
		)
	);
}

/**
 * Get the number of non-empty native WooCommerce product brand terms.
 *
 * @return int
 */
function vapestore_get_product_brand_count() {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return 0;
	}

	$count = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => true,
			'fields'     => 'count',
		)
	);

	return is_wp_error( $count ) ? 0 : (int) $count;
}

/**
 * Render one native product brand card.
 *
 * @param WP_Term $brand Product brand term.
 * @return void
 */
function vapestore_render_brand_card( $brand ) {
	if ( ! $brand instanceof WP_Term ) {
		return;
	}

	$link = get_term_link( $brand, 'product_brand' );

	if ( is_wp_error( $link ) ) {
		return;
	}
	?>
	<a class="vapestore-brand-card" href="<?php echo esc_url( $link ); ?>">
		<span class="vapestore-brand-card__name"><?php echo esc_html( $brand->name ); ?></span>
		<span class="vapestore-brand-card__count">
			<?php echo esc_html( sprintf( _n( '%s product', '%s products', $brand->count, 'vapestore' ), number_format_i18n( $brand->count ) ) ); ?>
		</span>
		<span class="vapestore-brand-card__action"><?php esc_html_e( 'View products', 'vapestore' ); ?> &rarr;</span>
	</a>
	<?php
}

/**
 * Render a grid of native product brand cards.
 *
 * @param WP_Term[] $brands Product brand terms.
 * @return void
 */
function vapestore_render_brand_grid( $brands ) {
	if ( empty( $brands ) || ! is_array( $brands ) ) {
		return;
	}
	?>
	<div class="vapestore-brand-grid">
		<?php foreach ( $brands as $brand ) : ?>
			<?php vapestore_render_brand_card( $brand ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Determine whether the current request is for the virtual brand directory.
 *
 * @return bool
 */
function vapestore_is_shop_by_brand_request() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

	return 'shop-by-brand' === trim( (string) $request_path, '/' );
}

/**
 * Serve the lightweight brand directory without creating a persistent Page record.
 *
 * @return void
 */
function vapestore_render_shop_by_brand_directory() {
	if ( ! vapestore_is_shop_by_brand_request() ) {
		return;
	}

	global $wp_query;

	if ( $wp_query instanceof WP_Query ) {
		$wp_query->is_404 = false;
		$wp_query->is_page = true;
	}

	status_header( 200 );
	require get_template_directory() . '/shop-by-brand.php';
	exit;
}
add_action( 'template_redirect', 'vapestore_render_shop_by_brand_directory', 0 );

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

/**
 * Register the public recently viewed product card endpoint.
 */
function vapestore_register_recently_viewed_route() {
	register_rest_route(
		'vapestore/v1',
		'/recently-viewed',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'vapestore_rest_recently_viewed_products',
			'permission_callback' => '__return_true',
			'args'                => array(
				'ids'   => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'limit' => array(
					'type'              => 'integer',
					'default'           => 4,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'vapestore_register_recently_viewed_route' );

/**
 * Normalize a requested product ID list.
 *
 * @param string|array<int|string> $raw_ids Raw product IDs.
 * @return array<int>
 */
function vapestore_normalize_recently_viewed_ids( $raw_ids ) {
	$raw_ids = is_array( $raw_ids ) ? $raw_ids : explode( ',', (string) $raw_ids );
	$ids     = array();

	foreach ( $raw_ids as $raw_id ) {
		$product_id = absint( $raw_id );

		if ( $product_id > 0 && ! in_array( $product_id, $ids, true ) ) {
			$ids[] = $product_id;
		}

		if ( count( $ids ) >= 8 ) {
			break;
		}
	}

	return $ids;
}

/**
 * Check whether a product may appear in recently viewed cards.
 *
 * @param WC_Product|false|null $product Product object.
 * @return bool
 */
function vapestore_is_recently_viewed_product_visible( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}

	$product_id = $product->get_id();

	return 'product' === get_post_type( $product_id )
		&& 'publish' === get_post_status( $product_id )
		&& $product->is_visible();
}

/**
 * Render public product cards for recently viewed products.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response
 */
function vapestore_rest_recently_viewed_products( $request ) {
	if ( ! function_exists( 'wc_get_product' ) || ! function_exists( 'woocommerce_product_loop_start' ) ) {
		return rest_ensure_response( array( 'html' => '' ) );
	}

	$ids   = vapestore_normalize_recently_viewed_ids( $request->get_param( 'ids' ) );
	$limit = min( 8, max( 1, absint( $request->get_param( 'limit' ) ) ) );

	if ( empty( $ids ) ) {
		return rest_ensure_response( array( 'html' => '' ) );
	}

	$ids = array_slice( $ids, 0, $limit );

	ob_start();

	woocommerce_product_loop_start();

	foreach ( $ids as $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! vapestore_is_recently_viewed_product_visible( $product ) ) {
			continue;
		}

		$GLOBALS['post']    = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $GLOBALS['post'] );

		wc_get_template_part( 'content', 'product' );
	}

	woocommerce_product_loop_end();
	wp_reset_postdata();

	return rest_ensure_response( array( 'html' => trim( ob_get_clean() ) ) );
}

/**
 * Render a frontend-populated recently viewed placeholder.
 *
 * @param string $context Product or home context.
 * @param int    $limit   Maximum visible cards.
 */
function vapestore_recently_viewed_placeholder( $context, $limit ) {
	?>
	<section
		class="vapestore-recently-viewed vapestore-recently-viewed--<?php echo esc_attr( $context ); ?>"
		data-vapestore-recently-viewed
		data-limit="<?php echo esc_attr( absint( $limit ) ); ?>"
		hidden
	>
		<div class="<?php echo 'home' === $context ? 'container' : 'vapestore-recently-viewed__inner'; ?>">
			<div class="home-section__heading vapestore-recently-viewed__heading">
				<h2><?php esc_html_e( 'Recently Viewed', 'vapestore' ); ?></h2>
			</div>
			<div class="vapestore-recently-viewed__products" data-vapestore-recently-viewed-products></div>
		</div>
	</section>
	<?php
}

/**
 * Show recently viewed products below the single product discovery area.
 */
function vapestore_single_product_recently_viewed() {
	vapestore_recently_viewed_placeholder( 'product', 4 );
}
add_action( 'woocommerce_after_single_product_summary', 'vapestore_single_product_recently_viewed', 25 );
