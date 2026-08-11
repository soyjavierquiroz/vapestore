<?php
/**
 * Template for normal WordPress pages.
 *
 * @package VapeStore
 */

get_header();
?>

<main class="site-main">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
