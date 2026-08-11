<?php
/**
 * VapeStore theme functions.
 *
 * @package VapeStore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up theme support and menus.
 */
function vapestore_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
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
