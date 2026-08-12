<?php
/**
 * Guide article.
 *
 * Guides are core posts rather than a third custom type: they carry the same
 * cat-category taxonomy as reviews and roundups, and nothing about a guide
 * needs a field the editor cannot express in prose. A CPT here would have
 * bought a menu item and cost a migration.
 *
 * No JSON-LD and no disclosure, same as the other singular templates.
 * plugins/e4c-compliance owns both, and it disclosures a guide too when the
 * post links to a monetised domain or has _e4c_post_affiliate set.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$e4c_dek = e4c_field( 'e4c_dek' ) ?: get_the_excerpt();
	?>
	<article <?php post_class( 'e4c-shell e4c-review' ); ?>>

		<div class="e4c-review__head">
			<div>
				<span class="e4c-tag"><?php esc_html_e( 'Guide', 'e4c' ); ?></span>
				<h1><?php the_title(); ?></h1>

				<?php if ( $e4c_dek ) : ?>
					<p class="e4c-review__dek"><?php echo esc_html( $e4c_dek ); ?></p>
				<?php endif; ?>

				<p class="e4c-review__byline">
					<?php
					printf(
						/* translators: 1: author name, 2: publication date. */
						esc_html__( 'By %1$s, %2$s', 'e4c' ),
						esc_html( get_the_author() ),
						esc_html( get_the_date() )
					);

					/*
					 * Only shown when the post really was edited after publishing, which
					 * matches the condition e4c-compliance uses before emitting
					 * dateModified. If the two ever disagree the markup claims something
					 * the page does not say.
					 */
					if ( get_the_modified_date( 'Ymd' ) > get_the_date( 'Ymd' ) ) {
						printf(
							/* translators: %s: date the guide was last revised. */
							' &middot; ' . esc_html__( 'Updated %s', 'e4c' ),
							esc_html( get_the_modified_date() )
						);
					}
					?>
				</p>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="e4c-hero__figure">
					<?php e4c_hero_image( (int) get_post_thumbnail_id(), 'e4c-hero' ); ?>
					<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
						<figcaption class="e4c-buy__note"><?php echo esc_html( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>

		<div class="e4c-article"><?php the_content(); ?></div>

		<?php
		$e4c_terms = get_the_terms( get_the_ID(), 'cat-category' );

		if ( $e4c_terms && ! is_wp_error( $e4c_terms ) ) :
			?>
			<nav class="e4c-facets e4c-facets--footer" aria-label="<?php esc_attr_e( 'Categories', 'e4c' ); ?>">
				<?php foreach ( $e4c_terms as $e4c_term ) : ?>
					<a class="e4c-facet" href="<?php echo esc_url( (string) get_term_link( $e4c_term ) ); ?>">
						<?php echo esc_html( $e4c_term->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/related', 'reviews' ); ?>
	</article>
	<?php
endwhile;

get_footer();
