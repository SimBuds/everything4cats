<?php
/**
 * Fallback archive.
 *
 * As of 2026-08-12 the category, roundup, guide, search and 404 templates all
 * exist, so this catches much less than it used to. What still lands here: the
 * roundup post-type archive at /best/, tag archives, author archives, date
 * archives, and the blog posts index when a static front page is assigned.
 *
 * Kept general on purpose. Each of those is a list of cards with a heading, and
 * a template per case would be five copies of this file differing only in how
 * they derive the title, which the_archive_title() already does.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell e4c-section">
	<?php if ( have_posts() ) : ?>
		<div class="e4c-section__head">
			<h2>
				<?php
				if ( is_home() && ! is_front_page() ) {
					single_post_title();
				} elseif ( is_archive() ) {
					the_archive_title();
				} elseif ( is_search() ) {
					printf( esc_html__( 'Results for %s', 'e4c' ), '<em>' . esc_html( get_search_query() ) . '</em>' );
				} else {
					esc_html_e( 'Latest', 'e4c' );
				}
				?>
			</h2>
		</div>

		<div class="e4c-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'post' );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination( array(
			'class'     => 'e4c-pagination',
			'mid_size'  => 1,
			'prev_text' => __( 'Newer', 'e4c' ),
			'next_text' => __( 'Older', 'e4c' ),
		) );
		?>
	<?php else : ?>
		<h2><?php esc_html_e( 'Nothing here yet', 'e4c' ); ?></h2>
		<p><?php esc_html_e( 'No posts matched. Try the search, or start from the home page.', 'e4c' ); ?></p>
		<?php e4c_button( home_url( '/' ), __( 'Back to the home page', 'e4c' ), 'primary' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
