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
