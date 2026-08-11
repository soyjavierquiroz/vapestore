<?php
/**
 * Fixed homepage template.
 *
 * @package VapeStore
 */

get_header();

$has_acf = function_exists( 'get_field' );

$hero_eyebrow         = $has_acf ? get_field( 'home_hero_eyebrow' ) : '';
$hero_title           = $has_acf ? get_field( 'home_hero_title' ) : '';
$hero_text            = $has_acf ? get_field( 'home_hero_text' ) : '';
$hero_image_id        = $has_acf ? get_field( 'home_hero_image' ) : '';
$hero_primary_label   = $has_acf ? get_field( 'home_hero_primary_label' ) : '';
$hero_secondary_label = $has_acf ? get_field( 'home_hero_secondary_label' ) : '';

$hero_eyebrow         = $hero_eyebrow ? $hero_eyebrow : __( 'VapeStore', 'vapestore' );
$hero_title           = $hero_title ? $hero_title : __( 'Everything you need, all in one place', 'vapestore' );
$hero_text            = $hero_text ? $hero_text : __( 'Explore our product selection and shop from a simple, reliable storefront.', 'vapestore' );
$hero_primary_label   = $hero_primary_label ? $hero_primary_label : __( 'Shop Now', 'vapestore' );
$hero_secondary_label = $hero_secondary_label ? $hero_secondary_label : __( 'Learn More', 'vapestore' );

$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$about_url = home_url( '/about/' );

$benefit_fallbacks = array(
	array(
		'title' => __( 'Product Selection', 'vapestore' ),
		'text'  => __( 'Explore products across our available categories.', 'vapestore' ),
	),
	array(
		'title' => __( 'Customer Support', 'vapestore' ),
		'text'  => __( 'Get help when you need it.', 'vapestore' ),
	),
	array(
		'title' => __( 'Easy Shopping', 'vapestore' ),
		'text'  => __( 'Browse, choose and order from one storefront.', 'vapestore' ),
	),
	array(
		'title' => __( 'Secure Checkout', 'vapestore' ),
		'text'  => __( 'Checkout powered by WooCommerce.', 'vapestore' ),
	),
);

$benefits = array();

for ( $i = 1; $i <= 4; $i++ ) {
	$title      = $has_acf ? get_field( 'home_benefit_' . $i . '_title' ) : '';
	$text       = $has_acf ? get_field( 'home_benefit_' . $i . '_text' ) : '';
	$benefits[] = array(
		'title' => $title ? $title : $benefit_fallbacks[ $i - 1 ]['title'],
		'text'  => $text ? $text : $benefit_fallbacks[ $i - 1 ]['text'],
	);
}

$about_eyebrow      = $has_acf ? get_field( 'home_about_eyebrow' ) : '';
$about_title        = $has_acf ? get_field( 'home_about_title' ) : '';
$about_text         = $has_acf ? get_field( 'home_about_text' ) : '';
$about_image_id     = $has_acf ? get_field( 'home_about_image' ) : '';
$about_button_label = $has_acf ? get_field( 'home_about_button_label' ) : '';

$about_eyebrow      = $about_eyebrow ? $about_eyebrow : __( 'About', 'vapestore' );
$about_title        = $about_title ? $about_title : __( 'A simple storefront for everyday shopping', 'vapestore' );
$about_text         = $about_text ? $about_text : __( 'Use this section to introduce the store with clear, editable information for visitors.', 'vapestore' );
$about_button_label = $about_button_label ? $about_button_label : __( 'About Us', 'vapestore' );

$featured_products = function_exists( 'wc_get_products' )
	? wc_get_products(
		array(
			'featured' => true,
			'limit'    => 4,
			'return'   => 'ids',
			'status'   => 'publish',
		)
	)
	: array();

$new_products = function_exists( 'wc_get_products' )
	? wc_get_products(
		array(
			'limit'   => 4,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'ids',
			'status'  => 'publish',
		)
	)
	: array();
?>

<main class="home">
	<section class="home-hero">
		<div class="container home-hero__inner">
			<div class="home-hero__content">
				<p class="home-eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></p>
				<h1><?php echo esc_html( $hero_title ); ?></h1>
				<p><?php echo esc_html( $hero_text ); ?></p>
				<div class="home-hero__actions">
					<a class="button button--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( $hero_primary_label ); ?></a>
					<a class="button button--secondary" href="<?php echo esc_url( $about_url ); ?>"><?php echo esc_html( $hero_secondary_label ); ?></a>
				</div>
			</div>
			<?php if ( $hero_image_id ) : ?>
				<div class="home-hero__media">
					<?php echo wp_get_attachment_image( (int) $hero_image_id, 'large' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="home-benefits">
		<div class="container home-benefits__grid">
			<?php foreach ( $benefits as $benefit ) : ?>
				<section class="home-benefit">
					<h2><?php echo esc_html( $benefit['title'] ); ?></h2>
					<p><?php echo esc_html( $benefit['text'] ); ?></p>
				</section>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="home-section home-categories">
		<div class="container">
			<h2><?php esc_html_e( 'Shop by Category', 'vapestore' ); ?></h2>
			<?php
			$product_categories = array();

			if ( taxonomy_exists( 'product_cat' ) ) {
				$product_categories = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'parent'     => 0,
						'hide_empty' => true,
						'number'     => 4,
					)
				);
			}

			if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) :
				?>
				<div class="home-categories__grid">
					<?php foreach ( $product_categories as $category ) : ?>
						<a class="home-category" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
							<?php
							$thumbnail_id = (int) get_term_meta( $category->term_id, 'thumbnail_id', true );

							if ( $thumbnail_id ) {
								echo wp_get_attachment_image( $thumbnail_id, 'woocommerce_thumbnail' );
							}
							?>
							<span><?php echo esc_html( $category->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="home-empty"><?php esc_html_e( 'Product categories will appear here.', 'vapestore' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="home-section home-products">
		<div class="container">
			<h2><?php esc_html_e( 'Featured Products', 'vapestore' ); ?></h2>
			<?php if ( ! empty( $featured_products ) ) : ?>
				<?php echo do_shortcode( '[featured_products limit="4" columns="4"]' ); ?>
			<?php else : ?>
				<p class="home-empty"><?php esc_html_e( 'Featured products will appear here.', 'vapestore' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="home-about">
		<div class="container home-about__inner">
			<div class="home-about__content">
				<p class="home-eyebrow"><?php echo esc_html( $about_eyebrow ); ?></p>
				<h2><?php echo esc_html( $about_title ); ?></h2>
				<p><?php echo esc_html( $about_text ); ?></p>
				<a class="button button--primary" href="<?php echo esc_url( $about_url ); ?>"><?php echo esc_html( $about_button_label ); ?></a>
			</div>
			<?php if ( $about_image_id ) : ?>
				<div class="home-about__media">
					<?php echo wp_get_attachment_image( (int) $about_image_id, 'large' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="home-section home-products">
		<div class="container">
			<h2><?php esc_html_e( 'New Arrivals', 'vapestore' ); ?></h2>
			<?php if ( ! empty( $new_products ) ) : ?>
				<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
			<?php else : ?>
				<p class="home-empty"><?php esc_html_e( 'New products will appear here.', 'vapestore' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
