<?php
/**
 * Development helper for storefront information pages.
 *
 * @package VapeStoreCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VAPESTORE_DEV_INFORMATION_PAGES_TRIGGER = 'vapestore_prepare_information_pages';

/**
 * Determine whether this helper may run in the current environment.
 *
 * @return bool
 */
function vapestore_dev_information_pages_is_allowed_environment() {
	return function_exists( 'wp_get_environment_type' ) && 'development' === wp_get_environment_type();
}

/**
 * Get the information page definitions.
 *
 * @return array<string, array{title: string, content: string}>
 */
function vapestore_dev_information_pages_get_definitions() {
	return array(
		'about-us'         => array(
			'title'   => 'About Us',
			'content' => '<p>Final company story and brand description pending client approval.</p>',
		),
		'contact-us'       => array(
			'title'   => 'Contact Us',
			'content' => '<p>Final phone, email, store address, hours, and social links pending.</p>',
		),
		'faq'              => array(
			'title'   => 'FAQ',
			'content' => '<h2>Ordering</h2><p>Pending client-approved answer.</p><h2>Shipping</h2><p>Pending client-approved answer.</p><h2>Returns</h2><p>Pending client-approved answer.</p><h2>Payments</h2><p>Pending client-approved answer.</p><h2>Age Requirements</h2><p>Pending client-approved answer.</p>',
		),
		'shipping-returns' => array(
			'title'   => 'Shipping & Returns',
			'content' => '<p>Final shipping zones, rates, delivery times, and return policy pending.</p>',
		),
		'privacy-policy'   => array(
			'title'   => 'Privacy Policy',
			'content' => '<p>Final privacy policy pending business/legal approval.</p>',
		),
		'terms-conditions' => array(
			'title'   => 'Terms & Conditions',
			'content' => '<p>Final terms pending business/legal approval.</p>',
		),
		'age-policy'       => array(
			'title'   => 'Age Policy',
			'content' => '<p>Final age-verification and purchase eligibility policy pending.</p>',
		),
	);
}

/**
 * Find a page by preferred slug or title.
 *
 * @param string $slug  Preferred page slug.
 * @param string $title Page title.
 * @return WP_Post|null
 */
function vapestore_dev_information_pages_find_page( $slug, $title ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	if ( $page instanceof WP_Post ) {
		return $page;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'title'                  => $title,
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$found = $query->have_posts() ? $query->posts[0] : null;
	wp_reset_postdata();

	return $found instanceof WP_Post ? $found : null;
}

/**
 * Create any missing information pages.
 *
 * @return array{created: string[], reused: string[], privacy_page_id: int}
 */
function vapestore_dev_information_pages_prepare() {
	$created         = array();
	$reused          = array();
	$updated         = array();
	$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

	foreach ( vapestore_dev_information_pages_get_definitions() as $slug => $page_data ) {
		$page = null;

		if ( 'privacy-policy' === $slug && $privacy_page_id ) {
			$assigned_page = get_post( $privacy_page_id );

			if ( $assigned_page instanceof WP_Post && 'page' === $assigned_page->post_type ) {
				$page = $assigned_page;
			}
		}

		if ( ! $page instanceof WP_Post ) {
			$page = vapestore_dev_information_pages_find_page( $slug, $page_data['title'] );
		}

		if ( $page instanceof WP_Post ) {
			if ( 'privacy-policy' === $slug ) {
				$privacy_update = array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
					'post_title'  => $page_data['title'],
					'post_name'   => $slug,
					'post_content' => $page_data['content'],
				);

				$updated_page_id = wp_update_post( $privacy_update, true );

				if ( ! is_wp_error( $updated_page_id ) ) {
					$updated[] = $page_data['title'];
				}
			}

			$reused[] = $page_data['title'];
			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page_data['title'],
				'post_name'    => $slug,
				'post_content' => $page_data['content'],
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id ) {
			$created[] = $page_data['title'];

			if ( 'privacy-policy' === $slug && ! $privacy_page_id ) {
				update_option( 'wp_page_for_privacy_policy', (int) $page_id );
				$privacy_page_id = (int) $page_id;
			}
		}
	}

	return array(
		'created'         => $created,
		'reused'          => $reused,
		'updated'         => $updated,
		'privacy_page_id' => $privacy_page_id,
	);
}

/**
 * Handle the HTTP trigger for local/development page preparation.
 *
 * @return void
 */
function vapestore_dev_information_pages_handle_trigger() {
	if ( empty( $_GET[ VAPESTORE_DEV_INFORMATION_PAGES_TRIGGER ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! vapestore_dev_information_pages_is_allowed_environment() ) {
		wp_die( esc_html__( 'Information page preparation is only available in development.', 'vapestore-core' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to prepare information pages.', 'vapestore-core' ) );
	}

	$result = vapestore_dev_information_pages_prepare();

	wp_send_json_success( $result );
}
add_action( 'init', 'vapestore_dev_information_pages_handle_trigger' );
