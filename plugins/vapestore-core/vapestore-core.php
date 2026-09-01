<?php
/**
 * Plugin Name: VapeStore Core
 * Description: Store-specific functionality for VapeStore.
 * Version: 0.1.0
 * Text Domain: vapestore-core
 *
 * @package VapeStoreCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/dev-seed-variable-product.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/dev-information-pages.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/bulk-variation-order.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/woocommerce-email-branding.php';

/**
 * Check whether the current user may upload trusted SVG brand assets.
 *
 * @return bool
 */
function vapestore_core_can_upload_svg() {
	return current_user_can( 'upload_files' ) && current_user_can( 'manage_options' );
}

/**
 * Allow SVG uploads for trusted administrators only.
 *
 * @param array<string, string> $mime_types Allowed MIME types.
 * @return array<string, string>
 */
function vapestore_core_upload_mimes( $mime_types ) {
	if ( vapestore_core_can_upload_svg() ) {
		// SVG uploads are intentionally limited to trusted administrators.
		$mime_types['svg'] = 'image/svg+xml';
	}

	return $mime_types;
}
add_filter( 'upload_mimes', 'vapestore_core_upload_mimes' );

/**
 * Ensure WordPress recognizes SVG files during upload checks.
 *
 * @param array<string, string|false> $file_type_ext File type and extension data.
 * @param string                      $file          Full path to the file.
 * @param string                      $filename      The name of the file.
 * @param array<string, string>       $mime_types    Allowed MIME types.
 * @return array<string, string|false>
 */
function vapestore_core_check_svg_filetype_and_ext( $file_type_ext, $file, $filename, $mime_types ) {
	unset( $file, $mime_types );

	if ( ! vapestore_core_can_upload_svg() ) {
		return $file_type_ext;
	}

	if ( 'svg' !== strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		return $file_type_ext;
	}

	$file_type_ext['ext']  = 'svg';
	$file_type_ext['type'] = 'image/svg+xml';

	return $file_type_ext;
}
add_filter( 'wp_check_filetype_and_ext', 'vapestore_core_check_svg_filetype_and_ext', 10, 4 );

/**
 * Enqueue branded styles for the native WordPress login screen.
 *
 * @return void
 */
function vapestore_core_login_enqueue_styles() {
	$asset_path = plugin_dir_path( __FILE__ ) . 'assets/css/login.css';
	$asset_url  = plugin_dir_url( __FILE__ ) . 'assets/css/login.css';
	$version    = file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : '0.1.0';

	wp_enqueue_style( 'vapestore-core-login', $asset_url, array(), $version );

	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
	$logo_url       = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : '';

	if ( $logo_url ) {
		$custom_css = sprintf(
			'.login h1 a{background-image:url("%s");}',
			esc_url_raw( $logo_url )
		);

		wp_add_inline_style( 'vapestore-core-login', $custom_css );
	}
}
add_action( 'login_enqueue_scripts', 'vapestore_core_login_enqueue_styles' );

/**
 * Point the native login logo link to the site home page.
 *
 * @return string
 */
function vapestore_core_login_header_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'vapestore_core_login_header_url' );

/**
 * Use the site name for the native login logo accessible text.
 *
 * @return string
 */
function vapestore_core_login_header_text() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'vapestore_core_login_header_text' );
