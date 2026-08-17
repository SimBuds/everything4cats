<?php
/**
 * Search results, with post-type facets.
 *
 * The facets are links carrying `post_type` alongside the existing `s`, not a
 * JavaScript filter. Three reasons: the result count is already known
 * server-side, a filtered view gets a shareable URL, and it works before any
 * script loads. WordPress reads `post_type` from the query string on a search
 * request without any extra wiring.
 *
 * Counts come from one extra query per facet. That is three small queries on a
 * page nobody hits at volume, and the alternative, rendering a facet that turns
 * out to be empty, is worse than the cost.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$e4c_query   = get_search_query();
$e4c_current = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

/**
 * Counts matches of one post type for the current search term.
 *
 * @param string $post_type Post type to count, or 'any' for everything.
 * @param string $term      The search term.
 * @return int
 */
$e4c_count = static function ( string $post_type, string $term ): int {
	$found = new WP_Query( array(
		's'                      => $term,
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'fields'                 => 'ids',
		'no_found_rows'          => false,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	) );

	return (int) $found->found_posts;
};

// Plural names come from the same map the cards use, so a filter pill and the
// tag on the card it filters to can never disagree about what a thing is
// called. They did: this list said Guides while every card said Post.
$e4c_facets = array(
	''        => __( 'Everything', 'e4c' ),
	'review'  => e4c_type_meta( 'review' )['plural'],
	'roundup' => e4c_type_meta( 'roundup' )['plural'],
	'post'    => e4c_type_meta( 'post' )['plural'],
);
?>
<div class="e4c-shell e4c-section">
	<div class="e4c-section__head">
		<div>
			<h1 class="e4c-page-title">
				<?php
				printf(
					/* translators: %s: the search term, already escaped and wrapped. */
					esc_html__( 'Results for %s', 'e4c' ),
					'<em>' . esc_html( $e4c_query ) . '</em>'
				);
				?>
			</h1>
			<p class="e4c-page-lede">
				<?php
				printf(
					/* translators: %d: number of matching items. */
					esc_html( _n( '%d match', '%d matches', (int) $GLOBALS['wp_query']->found_posts, 'e4c' ) ),
					(int) $GLOBALS['wp_query']->found_posts
				);
				?>
			</p>
		</div>

		<?php get_search_form(); ?>
	</div>

	<nav class="e4c-facets" aria-label="<?php esc_attr_e( 'Filter by type', 'e4c' ); ?>">
		<?php
		foreach ( $e4c_facets as $e4c_slug => $e4c_label ) :
			$e4c_total = $e4c_count( $e4c_slug ?: 'any', $e4c_query );

			// A facet that would return nothing is not offered. Better than a
			// clickable dead end that reports zero after the navigation.
			if ( ! $e4c_total ) {
				continue;
			}

			$e4c_url = $e4c_slug
				? add_query_arg( array( 's' => rawurlencode( $e4c_query ), 'post_type' => $e4c_slug ), home_url( '/' ) )
				: add_query_arg( array( 's' => rawurlencode( $e4c_query ) ), home_url( '/' ) );

			$e4c_on = ( $e4c_slug === $e4c_current );
			?>
			<a
				class="e4c-facet<?php echo $e4c_on ? ' e4c-facet--on' : ''; ?>"
				href="<?php echo esc_url( $e4c_url ); ?>"
				<?php echo $e4c_on ? 'aria-current="true"' : ''; ?>
			>
				<?php echo esc_html( $e4c_label ); ?>
				<span class="e4c-facet__count"><?php echo esc_html( (string) $e4c_total ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php if ( have_posts() ) : ?>
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
		<div class="e4c-empty">
			<h2><?php esc_html_e( 'Nothing matched that', 'e4c' ); ?></h2>
			<p><?php esc_html_e( 'Try a broader word. Searching for the kind of thing, like litter or scratching post, usually works better than a brand name.', 'e4c' ); ?></p>
			<div class="e4c-actions">
				<?php
				e4c_button( (string) get_post_type_archive_link( 'review' ), __( 'Browse every review', 'e4c' ), 'primary' );
				e4c_button( home_url( '/' ), __( 'Back to the home page', 'e4c' ), 'secondary' );
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
