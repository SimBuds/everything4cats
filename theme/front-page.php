<?php
/**
 * Home page.
 *
 * The hero and the closing patch are block patterns so they are editable in
 * Gutenberg; the two feeds below are queries, because a list of the newest
 * reviews should not be a thing anyone maintains by hand.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell">

	<?php
	/*
	 * A front page assigned in Settings > Reading renders its own blocks, which
	 * is how the hero and patch patterns get edited. With no page assigned the
	 * theme falls back to the pattern markup so a fresh install is not blank.
	 */
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	else :
		echo do_blocks( '<!-- wp:pattern {"slug":"e4c/home-hero"} /-->' );
	endif;
	?>

	<?php
	$e4c_reviews = new WP_Query( array(
		'post_type'           => 'review',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_reviews->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-latest">
			<div class="e4c-section__head">
				<h2 id="e4c-latest"><?php esc_html_e( 'Recently tested', 'e4c' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'review' ) ); ?>"><?php esc_html_e( 'All reviews', 'e4c' ); ?></a>
			</div>

			<div class="e4c-grid">
				<?php
				while ( $e4c_reviews->have_posts() ) :
					$e4c_reviews->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();

	$e4c_roundups = new WP_Query( array(
		'post_type'           => 'roundup',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_roundups->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-picks">
			<div class="e4c-section__head">
				<h2 id="e4c-picks"><?php esc_html_e( 'Our picks', 'e4c' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'roundup' ) ); ?>"><?php esc_html_e( 'All our picks', 'e4c' ); ?></a>
			</div>

			<div class="e4c-grid">
				<?php
				while ( $e4c_roundups->have_posts() ) :
					$e4c_roundups->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>

	<?php echo do_blocks( '<!-- wp:pattern {"slug":"e4c/newsletter-patch"} /-->' ); ?>
</div>
<?php
get_footer();
