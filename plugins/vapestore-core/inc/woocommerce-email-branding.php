<?php
/**
 * WooCommerce transactional email branding.
 *
 * @package VapeStoreCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the raster custom logo URL for broad email-client compatibility.
 *
 * @return string
 */
function vapestore_core_get_email_logo_url() {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( ! $custom_logo_id ) {
		return '';
	}

	$mime_type = get_post_mime_type( $custom_logo_id );

	if ( 'image/svg+xml' === $mime_type ) {
		return '';
	}

	$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );

	return $logo_url ? $logo_url : '';
}

/**
 * Use the current WordPress custom logo in WooCommerce emails when no email logo is set.
 *
 * @param mixed $value Stored WooCommerce email header image URL.
 * @return mixed
 */
function vapestore_core_email_header_image( $value ) {
	if ( ! empty( $value ) ) {
		return $value;
	}

	$logo_url = vapestore_core_get_email_logo_url();

	return $logo_url ? $logo_url : $value;
}
add_filter( 'option_woocommerce_email_header_image', 'vapestore_core_email_header_image' );

/**
 * Keep the email logo at a conservative width when the setting is unset.
 *
 * @param mixed $value Stored WooCommerce email logo width.
 * @return mixed
 */
function vapestore_core_email_header_image_width( $value ) {
	return empty( $value ) ? '200' : $value;
}
add_filter( 'option_woocommerce_email_header_image_width', 'vapestore_core_email_header_image_width' );

/**
 * Supply brand defaults for empty WooCommerce email color settings.
 *
 * @param mixed $value Stored option value.
 * @return mixed
 */
function vapestore_core_email_base_color( $value ) {
	return empty( $value ) ? '#47EF03' : $value;
}
add_filter( 'option_woocommerce_email_base_color', 'vapestore_core_email_base_color' );

/**
 * Supply a light neutral email background when the setting is empty.
 *
 * @param mixed $value Stored option value.
 * @return mixed
 */
function vapestore_core_email_background_color( $value ) {
	return empty( $value ) ? '#F4F4F4' : $value;
}
add_filter( 'option_woocommerce_email_background_color', 'vapestore_core_email_background_color' );

/**
 * Supply a white content background when the setting is empty.
 *
 * @param mixed $value Stored option value.
 * @return mixed
 */
function vapestore_core_email_body_background_color( $value ) {
	return empty( $value ) ? '#FFFFFF' : $value;
}
add_filter( 'option_woocommerce_email_body_background_color', 'vapestore_core_email_body_background_color' );

/**
 * Supply readable body text when the setting is empty.
 *
 * @param mixed $value Stored option value.
 * @return mixed
 */
function vapestore_core_email_text_color( $value ) {
	return empty( $value ) ? '#202020' : $value;
}
add_filter( 'option_woocommerce_email_text_color', 'vapestore_core_email_text_color' );

/**
 * Supply restrained footer text color when the setting is empty.
 *
 * @param mixed $value Stored option value.
 * @return mixed
 */
function vapestore_core_email_footer_text_color( $value ) {
	return empty( $value ) ? '#606060' : $value;
}
add_filter( 'option_woocommerce_email_footer_text_color', 'vapestore_core_email_footer_text_color' );

/**
 * Use a safe brand footer option when WooCommerce still has an empty/default footer.
 *
 * @param mixed $value Stored WooCommerce footer text.
 * @return mixed
 */
function vapestore_core_email_footer_text_option( $value ) {
	$normalized_footer = strtolower( trim( wp_strip_all_tags( (string) $value ) ) );

	if ( '' === $normalized_footer || '{site_title}{store_address}' === $normalized_footer ) {
		return 'Escape N Vape Smoke Shop';
	}

	return $value;
}
add_filter( 'option_woocommerce_email_footer_text', 'vapestore_core_email_footer_text_option' );

/**
 * Use a safe brand footer when WooCommerce still has an empty/default footer.
 *
 * @param string        $footer_text Footer text.
 * @param WC_Email|null $email       Email instance.
 * @return string
 */
function vapestore_core_email_footer_text( $footer_text, $email = null ) {
	unset( $email );

	return vapestore_core_email_footer_text_option( $footer_text );
}
add_filter( 'woocommerce_email_footer_text', 'vapestore_core_email_footer_text', 20, 2 );

/**
 * Brand WooCommerce email setting defaults without overwriting stored settings.
 *
 * @param array<int, array<string, mixed>> $settings Email settings.
 * @return array<int, array<string, mixed>>
 */
function vapestore_core_email_settings_defaults( $settings ) {
	$defaults = array(
		'woocommerce_email_header_image_width'     => '200',
		'woocommerce_email_base_color'            => '#47EF03',
		'woocommerce_email_background_color'      => '#F4F4F4',
		'woocommerce_email_body_background_color' => '#FFFFFF',
		'woocommerce_email_text_color'            => '#202020',
		'woocommerce_email_footer_text_color'     => '#606060',
		'woocommerce_email_font_family'           => 'Helvetica',
	);

	foreach ( $settings as $index => $setting ) {
		if ( isset( $setting['id'], $defaults[ $setting['id'] ] ) ) {
			$settings[ $index ]['default'] = $defaults[ $setting['id'] ];
		}
	}

	return $settings;
}
add_filter( 'woocommerce_email_settings', 'vapestore_core_email_settings_defaults' );

/**
 * Add small, email-safe brand refinements to WooCommerce's native email CSS.
 *
 * @param string   $css   WooCommerce email CSS.
 * @param WC_Email $email Email instance.
 * @return string
 */
function vapestore_core_email_styles( $css, $email ) {
	unset( $email );

	$brand_css = '
		body,
		#outer_wrapper {
			background-color: #F4F4F4;
		}

		#wrapper {
			padding: 32px 0;
		}

		#template_container {
			border: 1px solid #DADADA;
		}

		#template_header,
		#template_header h1,
		#template_header h1 a {
			background-color: #000000;
			color: #FFFFFF;
		}

		#header_wrapper {
			padding: 28px 32px;
		}

		#template_header_image {
			background-color: #000000;
			padding: 24px 32px 12px;
		}

		#template_header_image p {
			text-align: left;
		}

		#template_header_image img {
			height: auto;
			max-width: 200px;
		}

		.email-logo-text,
		.email-logo-text a {
			color: #FFFFFF;
			font-weight: 700;
		}

		#body_content,
		#body_content_inner {
			color: #202020;
			font-family: Helvetica, Arial, sans-serif;
			line-height: 1.55;
		}

		#body_content a {
			color: #0E7814;
			font-weight: 600;
		}

		a.button,
		.button {
			background-color: #47EF03;
			border-radius: 4px;
			color: #000000;
			font-weight: 700;
		}

		#body_content table .email-order-details th,
		#body_content table .email-order-details td,
		#body_content table.order_details th,
		#body_content table.order_details td {
			border-color: #DADADA;
			color: #202020;
			line-height: 1.45;
		}

		#template_footer #credit {
			color: #606060;
			font-family: Helvetica, Arial, sans-serif;
		}
	';

	return $css . "\n" . $brand_css;
}
add_filter( 'woocommerce_email_styles', 'vapestore_core_email_styles', 20, 2 );
