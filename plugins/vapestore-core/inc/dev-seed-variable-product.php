<?php
/**
 * Development seed for one demo WooCommerce variable product.
 *
 * @package VapeStoreCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VAPESTORE_DEV_SEED_FUME_OPTION     = 'vapestore_dev_seed_fume_infinity_4500';
const VAPESTORE_DEV_SEED_FUME_NOTICE     = 'vapestore_dev_seed_fume_notice';
const VAPESTORE_DEV_SEED_FUME_ACTION     = 'vapestore_seed_fume_infinity_4500';
const VAPESTORE_DEV_SEED_FUME_PARENT_SKU = 'DEMO-FUME-INFINITY-4500';

/**
 * Determine whether this temporary dev seed is allowed to run.
 *
 * @return bool
 */
function vapestore_dev_seed_fume_is_allowed_environment() {
	return function_exists( 'wp_get_environment_type' ) && 'development' === wp_get_environment_type();
}

/**
 * Determine whether the current admin user can run the seed.
 *
 * @return bool
 */
function vapestore_dev_seed_fume_current_user_can_run() {
	return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
}

/**
 * Get the seed flavor definitions.
 *
 * @return array<int, array{name: string, sku: string, price: string, stock: int}>
 */
function vapestore_dev_seed_fume_get_variations() {
	return array(
		array( 'name' => 'Apple Watermelon', 'sku' => 'DEMO-FUME-INF-AW', 'price' => '14.99', 'stock' => 12 ),
		array( 'name' => 'Blueberry Mint', 'sku' => 'DEMO-FUME-INF-BM', 'price' => '14.99', 'stock' => 8 ),
		array( 'name' => 'Cotton Candy', 'sku' => 'DEMO-FUME-INF-CC', 'price' => '14.99', 'stock' => 0 ),
		array( 'name' => 'Dragon Fruit', 'sku' => 'DEMO-FUME-INF-DF', 'price' => '14.99', 'stock' => 15 ),
		array( 'name' => 'Grape', 'sku' => 'DEMO-FUME-INF-GR', 'price' => '14.99', 'stock' => 5 ),
		array( 'name' => 'Mint Ice', 'sku' => 'DEMO-FUME-INF-MI', 'price' => '14.99', 'stock' => 20 ),
		array( 'name' => 'Strawberry Mango', 'sku' => 'DEMO-FUME-INF-SM', 'price' => '14.99', 'stock' => 10 ),
		array( 'name' => 'Watermelon Ice', 'sku' => 'DEMO-FUME-INF-WI', 'price' => '14.99', 'stock' => 7 ),
	);
}

/**
 * Store a short admin notice for the next redirected request.
 *
 * @param string $type    Notice type.
 * @param string $message Notice message.
 * @return void
 */
function vapestore_dev_seed_fume_set_notice( $type, $message ) {
	set_transient(
		VAPESTORE_DEV_SEED_FUME_NOTICE,
		array(
			'type'    => sanitize_key( $type ),
			'message' => sanitize_text_field( $message ),
		),
		MINUTE_IN_SECONDS
	);
}

/**
 * Get an existing term or create it.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $name     Term name.
 * @param string $slug     Term slug.
 * @return WP_Term|WP_Error
 */
function vapestore_dev_seed_fume_ensure_term( $taxonomy, $name, $slug ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( $term instanceof WP_Term ) {
		return $term;
	}

	$term = get_term_by( 'name', $name, $taxonomy );

	if ( $term instanceof WP_Term ) {
		return $term;
	}

	$inserted = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );

	if ( is_wp_error( $inserted ) ) {
		if ( $inserted->get_error_data( 'term_exists' ) ) {
			return get_term( (int) $inserted->get_error_data( 'term_exists' ), $taxonomy );
		}

		return $inserted;
	}

	return get_term( (int) $inserted['term_id'], $taxonomy );
}

/**
 * Register a newly-created WooCommerce attribute taxonomy for this request.
 *
 * @param string $taxonomy Taxonomy name.
 * @return bool
 */
function vapestore_dev_seed_fume_register_attribute_taxonomy( $taxonomy ) {
	if ( taxonomy_exists( $taxonomy ) ) {
		return true;
	}

	$attribute_slug = wc_attribute_taxonomy_slug( $taxonomy );
	$attribute_id   = wc_attribute_taxonomy_id_by_name( $attribute_slug );
	$attribute      = $attribute_id ? wc_get_attribute( $attribute_id ) : null;

	if ( ! $attribute ) {
		return false;
	}

	global $wc_product_attributes;

	if ( ! is_array( $wc_product_attributes ) ) {
		$wc_product_attributes = array();
	}

	$wc_product_attributes[ $taxonomy ] = (object) array(
		'attribute_id'      => $attribute->id,
		'attribute_name'    => $attribute_slug,
		'attribute_label'   => $attribute->name,
		'attribute_type'    => $attribute->type,
		'attribute_orderby' => $attribute->order_by,
		'attribute_public'  => $attribute->has_archives ? 1 : 0,
	);

	register_taxonomy(
		$taxonomy,
		array( 'product' ),
		array(
			'hierarchical'          => false,
			'update_count_callback' => '_update_post_term_count',
			'label'                 => $attribute->name,
			'public'                => $attribute->has_archives,
			'show_ui'               => true,
			'show_in_quick_edit'    => false,
			'show_in_menu'          => false,
			'meta_box_cb'           => false,
			'query_var'             => $attribute->has_archives,
			'rewrite'               => false,
			'sort'                  => false,
			'show_in_nav_menus'     => false,
			'capabilities'          => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
		)
	);

	return taxonomy_exists( $taxonomy );
}

/**
 * Ensure the global Flavor product attribute exists and is usable.
 *
 * @return int|WP_Error Attribute ID.
 */
function vapestore_dev_seed_fume_ensure_flavor_attribute() {
	$attribute_id = wc_attribute_taxonomy_id_by_name( 'flavor' );

	if ( ! $attribute_id ) {
		$attribute_id = wc_create_attribute(
			array(
				'name'         => 'Flavor',
				'slug'         => 'flavor',
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);

		if ( is_wp_error( $attribute_id ) ) {
			return $attribute_id;
		}
	}

	if ( ! vapestore_dev_seed_fume_register_attribute_taxonomy( 'pa_flavor' ) ) {
		return new WP_Error( 'vapestore_flavor_taxonomy_unavailable', 'The pa_flavor taxonomy is not available in this request.' );
	}

	return (int) $attribute_id;
}

/**
 * Create the FUME variable product seed.
 *
 * @return array{product_id: int, created: bool}|WP_Error
 */
function vapestore_dev_seed_fume_create_product() {
	if ( ! vapestore_dev_seed_fume_is_allowed_environment() ) {
		return new WP_Error( 'vapestore_seed_environment', 'This dev seed can only run when wp_get_environment_type() is development.' );
	}

	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Variable' ) || ! class_exists( 'WC_Product_Variation' ) ) {
		return new WP_Error( 'vapestore_seed_woocommerce_missing', 'WooCommerce product APIs are not available.' );
	}

	if ( 'success' === get_option( VAPESTORE_DEV_SEED_FUME_OPTION ) ) {
		return array(
			'product_id' => (int) wc_get_product_id_by_sku( VAPESTORE_DEV_SEED_FUME_PARENT_SKU ),
			'created'    => false,
		);
	}

	$existing_product_id = (int) wc_get_product_id_by_sku( VAPESTORE_DEV_SEED_FUME_PARENT_SKU );

	if ( $existing_product_id ) {
		update_option( VAPESTORE_DEV_SEED_FUME_OPTION, 'success', false );

		return array(
			'product_id' => $existing_product_id,
			'created'    => false,
		);
	}

	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return new WP_Error( 'vapestore_seed_brand_taxonomy_missing', 'The product_brand taxonomy is not available.' );
	}

	$brand = vapestore_dev_seed_fume_ensure_term( 'product_brand', 'FUME', 'fume' );
	if ( is_wp_error( $brand ) ) {
		return $brand;
	}

	$attribute_id = vapestore_dev_seed_fume_ensure_flavor_attribute();
	if ( is_wp_error( $attribute_id ) ) {
		return $attribute_id;
	}

	$flavor_term_ids = array();

	foreach ( vapestore_dev_seed_fume_get_variations() as $variation_data ) {
		$term = vapestore_dev_seed_fume_ensure_term( 'pa_flavor', $variation_data['name'], sanitize_title( $variation_data['name'] ) );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$flavor_term_ids[] = (int) $term->term_id;
	}

	$category = vapestore_dev_seed_fume_ensure_term( 'product_cat', 'Disposable Vape', 'disposable-vape' );
	if ( is_wp_error( $category ) ) {
		return $category;
	}

	$product = new WC_Product_Variable();
	$product->set_name( 'FUME Infinity Plus 4500 Puff Disposable' );
	$product->set_sku( VAPESTORE_DEV_SEED_FUME_PARENT_SKU );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( 'Demo variable product for storefront variation-table development.' );
	$product->set_description( '' );
	$product->set_manage_stock( false );
	$product->set_category_ids( array( (int) $category->term_id ) );

	$flavor_attribute = new WC_Product_Attribute();
	$flavor_attribute->set_id( (int) $attribute_id );
	$flavor_attribute->set_name( 'pa_flavor' );
	$flavor_attribute->set_options( $flavor_term_ids );
	$flavor_attribute->set_position( 0 );
	$flavor_attribute->set_visible( true );
	$flavor_attribute->set_variation( true );

	$product->set_attributes( array( 'pa_flavor' => $flavor_attribute ) );

	$product_id = $product->save();

	if ( ! $product_id ) {
		return new WP_Error( 'vapestore_seed_parent_failed', 'The parent variable product could not be saved.' );
	}

	$brand_result = wp_set_object_terms( $product_id, array( (int) $brand->term_id ), 'product_brand', false );
	if ( is_wp_error( $brand_result ) ) {
		return $brand_result;
	}

	$flavor_result = wp_set_object_terms( $product_id, $flavor_term_ids, 'pa_flavor', false );
	if ( is_wp_error( $flavor_result ) ) {
		return $flavor_result;
	}

	foreach ( vapestore_dev_seed_fume_get_variations() as $variation_data ) {
		if ( wc_get_product_id_by_sku( $variation_data['sku'] ) ) {
			continue;
		}

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_attributes( array( 'pa_flavor' => sanitize_title( $variation_data['name'] ) ) );
		$variation->set_sku( $variation_data['sku'] );
		$variation->set_regular_price( $variation_data['price'] );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( $variation_data['stock'] );
		$variation->set_stock_status( $variation_data['stock'] > 0 ? 'instock' : 'outofstock' );
		$variation->save();
	}

	WC_Product_Variable::sync( $product_id );
	wc_delete_product_transients( $product_id );

	update_option( VAPESTORE_DEV_SEED_FUME_OPTION, 'success', false );

	return array(
		'product_id' => $product_id,
		'created'    => true,
	);
}

/**
 * Handle the nonce-protected admin trigger.
 *
 * @return void
 */
function vapestore_dev_seed_fume_handle_admin_trigger() {
	if ( empty( $_GET['vapestore_seed_fume'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( ! vapestore_dev_seed_fume_current_user_can_run() ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to run this seed.', 'vapestore-core' ) );
	}

	check_admin_referer( VAPESTORE_DEV_SEED_FUME_ACTION );

	$result = vapestore_dev_seed_fume_create_product();

	if ( is_wp_error( $result ) ) {
		vapestore_dev_seed_fume_set_notice( 'error', $result->get_error_message() );
	} elseif ( $result['created'] ) {
		vapestore_dev_seed_fume_set_notice( 'success', 'FUME variable product created successfully.' );
	} else {
		vapestore_dev_seed_fume_set_notice( 'success', 'FUME variable product seed is already complete.' );
	}

	wp_safe_redirect( remove_query_arg( array( 'vapestore_seed_fume', '_wpnonce' ) ) );
	exit;
}
add_action( 'admin_init', 'vapestore_dev_seed_fume_handle_admin_trigger' );

/**
 * Show the dev seed trigger and redirected result notices.
 *
 * @return void
 */
function vapestore_dev_seed_fume_admin_notice() {
	if ( ! vapestore_dev_seed_fume_is_allowed_environment() || ! vapestore_dev_seed_fume_current_user_can_run() ) {
		return;
	}

	$notice = get_transient( VAPESTORE_DEV_SEED_FUME_NOTICE );

	if ( is_array( $notice ) && ! empty( $notice['message'] ) ) {
		delete_transient( VAPESTORE_DEV_SEED_FUME_NOTICE );

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( 'error' === $notice['type'] ? 'error' : 'success' ),
			esc_html( $notice['message'] )
		);

		return;
	}

	if ( 'success' === get_option( VAPESTORE_DEV_SEED_FUME_OPTION ) ) {
		return;
	}

	$url = wp_nonce_url(
		add_query_arg( 'vapestore_seed_fume', '1', admin_url( 'tools.php' ) ),
		VAPESTORE_DEV_SEED_FUME_ACTION
	);

	printf(
		'<div class="notice notice-info"><p>%1$s <a class="button button-primary" href="%2$s">%3$s</a></p></div>',
		esc_html__( 'VapeStore dev seed available:', 'vapestore-core' ),
		esc_url( $url ),
		esc_html__( 'Create FUME variable product', 'vapestore-core' )
	);
}
add_action( 'admin_notices', 'vapestore_dev_seed_fume_admin_notice' );
