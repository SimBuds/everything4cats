<?php
/**
 * Not found.
 *
 * Carries a route onward rather than an apology. Most 404s here will be a moved
 * review, so the newest three are offered directly and the search box is on the
 * page rather than one navigation away.
 *
 * plugins/redirection is the real fix for a URL that moved. This is what the
 * reader sees when nobody wrote the redirect yet.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell e4c-section">
	<div class="e4c-empty">
		<span class="e4c-tag"><?php esc_html_e( '404', 'e4c' ); ?></span>
		<h1 class="e4c-page-title"><?php esc_html_e( 'That page is not here', 'e4c' ); ?></h1>
		<p class="e4c-page-lede"><?php esc_html_e( 'It may have moved, or the link may have been mistyped. The search box below covers every review, roundup and guide.', 'e4c' ); ?></p>

		<?php get_search_form(); ?>

		<div class="e4c-actions">
			<?php
			e4c_button( home_url( '/' ), __( 'Back to the home page', 'e4c' ), 'primary' );
			e4c_button( (string) get_post_type_archive_link( 'review' ), __( 'Browse every review', 'e4c' ), 'secondary' );
			?>
		</div>
	</div>

	<?php
	$e4c_recent = new WP_Query( array(
		'post_type'           => array( 'review', 'roundup' ),
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_recent->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-404-recent">
			<div class="e4c-section__head">
				<h2 id="e4c-404-recent"><?php esc_html_e( 'Recently published', 'e4c' ); ?></h2>
			</div>

			<div class="e4c-grid">
				<?php
				while ( $e4c_recent->have_posts() ) :
					$e4c_recent->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;

	wp_reset_postdata();
	?>
</div>
<?php
get_footer();
