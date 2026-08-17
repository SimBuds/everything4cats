<?php
/**
 * Category archive.
 *
 * cat-category is registered by plugins/e4c-content across review, roundup and
 * post, which is the whole point of it: "litter boxes" collects the review, the
 * roundup and the guide on one page. So this is one taxonomy template rather
 * than three per-type archives, and the mixed result set is the feature.
 *
 * card-post.php already labels each result with its own post type, so a reader
 * can tell a review from a roundup without this template sorting them apart.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$e4c_term        = get_queried_object();
$e4c_description = $e4c_term instanceof WP_Term ? term_description( $e4c_term ) : '';
?>
<div class="e4c-shell e4c-section">
	<div class="e4c-section__head">
		<div>
			<span class="e4c-tag"><?php esc_html_e( 'Category', 'e4c' ); ?></span>
			<h1 class="e4c-page-title"><?php single_term_title(); ?></h1>

			<?php if ( $e4c_description ) : ?>
				<div class="e4c-page-lede"><?php echo wp_kses_post( $e4c_description ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Child terms as a sibling trail. Only rendered when there are any, so a
		 * flat category does not print an empty rail.
		 */
		$e4c_children = $e4c_term instanceof WP_Term
			? get_terms( array(
				'taxonomy'   => 'cat-category',
				'parent'     => $e4c_term->term_id,
				'hide_empty' => true,
			) )
			: array();

		if ( $e4c_children && ! is_wp_error( $e4c_children ) ) :
			?>
			<nav class="e4c-facets" aria-label="<?php esc_attr_e( 'Subcategories', 'e4c' ); ?>">
				<?php foreach ( $e4c_children as $e4c_child ) : ?>
					<a class="e4c-facet" href="<?php echo esc_url( get_term_link( $e4c_child ) ); ?>">
						<?php echo esc_html( $e4c_child->name ); ?>
						<span class="e4c-facet__count"><?php echo esc_html( (string) $e4c_child->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="e4c-grid e4c-grid--feature">
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
			<h2><?php esc_html_e( 'Nothing in this category yet', 'e4c' ); ?></h2>
			<p><?php esc_html_e( 'We have not published anything here so far. The reviews we have done are all in one place.', 'e4c' ); ?></p>
			<?php e4c_button( (string) get_post_type_archive_link( 'review' ), __( 'Browse every review', 'e4c' ), 'primary' ); ?>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
