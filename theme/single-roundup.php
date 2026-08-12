<?php
/**
 * Single roundup.
 *
 * The ranking comes from the e4c_picks repeater registered by
 * plugins/e4c-content. Its subfields are `review` (a post object or ID pointing
 * at the review), `award` (the short label, "Best overall") and `why` (one
 * sentence).
 *
 * Emits no JSON-LD and no disclosure, for the same reason single-review.php
 * does not: plugins/e4c-compliance owns Article schema on every singular view
 * and injects .art-disclose above the first paragraph. Two of either is worse
 * than one.
 *
 * Deliberately no aggregateRating markup on the picks. Marking an affiliate
 * comparison up as a rating is what Google's self-serving-review policy
 * prohibits, and the penalty is a manual action rather than a lost rich result.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$e4c_dek   = e4c_field( 'e4c_dek' );
	$e4c_picks = e4c_field( 'e4c_picks' );
	?>
	<article <?php post_class( 'e4c-shell e4c-review' ); ?>>

		<div class="e4c-review__head">
			<div>
				<span class="e4c-tag e4c-tag--second"><?php esc_html_e( 'Roundup', 'e4c' ); ?></span>
				<h1><?php the_title(); ?></h1>

				<?php if ( $e4c_dek ) : ?>
					<p class="e4c-review__dek"><?php echo esc_html( $e4c_dek ); ?></p>
				<?php endif; ?>

				<p class="e4c-review__byline">
					<?php
					printf(
						/* translators: 1: author name, 2: date the roundup was last updated. */
						esc_html__( 'By %1$s, updated %2$s', 'e4c' ),
						esc_html( get_the_author() ),
						esc_html( get_the_modified_date() )
					);
					?>
				</p>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="e4c-hero__figure">
					<?php e4c_hero_image( (int) get_post_thumbnail_id(), 'e4c-hero' ); ?>
				</figure>
			<?php endif; ?>
		</div>

		<div class="e4c-article"><?php the_content(); ?></div>

		<?php if ( $e4c_picks ) : ?>
			<section class="e4c-picks" aria-labelledby="e4c-picks-label">
				<h2 class="e4c-section-title" id="e4c-picks-label"><?php esc_html_e( 'The picks', 'e4c' ); ?></h2>

				<ol class="e4c-picks__list">
					<?php
					$e4c_rank = 0;

					foreach ( (array) $e4c_picks as $e4c_row ) :
						if ( ! is_array( $e4c_row ) ) {
							continue;
						}

						/*
						 * ACF returns a post object when the field is configured to, and a
						 * bare ID when it is not, or when ACF is inactive and this came
						 * from raw post meta. Both are normalised to an ID here rather
						 * than assuming either shape, because the fallback path is the one
						 * that runs when ACF is missing and is therefore the one least
						 * likely to have been exercised by hand.
						 */
						$e4c_ref = $e4c_row['review'] ?? 0;

						if ( $e4c_ref instanceof WP_Post ) {
							$e4c_ref = $e4c_ref->ID;
						}

						$e4c_ref   = (int) $e4c_ref;
						$e4c_award = $e4c_row['award'] ?? '';
						$e4c_why   = $e4c_row['why'] ?? '';

						// A pick with no linked review and no award is an empty repeater
						// row left behind in the editor, not content.
						if ( ! $e4c_ref && ! $e4c_award ) {
							continue;
						}

						++$e4c_rank;
						?>
						<li class="e4c-pick">
							<span class="e4c-pick__rank" aria-hidden="true"><?php echo esc_html( (string) $e4c_rank ); ?></span>

							<div class="e4c-pick__body">
								<?php if ( $e4c_award ) : ?>
									<span class="e4c-tag e4c-tag--second"><?php echo esc_html( $e4c_award ); ?></span>
								<?php endif; ?>

								<h3 class="e4c-pick__title">
									<?php if ( $e4c_ref && 'publish' === get_post_status( $e4c_ref ) ) : ?>
										<a href="<?php echo esc_url( (string) get_permalink( $e4c_ref ) ); ?>">
											<?php echo esc_html( get_the_title( $e4c_ref ) ); ?>
										</a>
									<?php elseif ( $e4c_ref ) : ?>
										<?php echo esc_html( get_the_title( $e4c_ref ) ); ?>
									<?php else : ?>
										<?php echo esc_html( $e4c_award ); ?>
									<?php endif; ?>
								</h3>

								<?php if ( $e4c_why ) : ?>
									<p class="e4c-pick__why"><?php echo esc_html( $e4c_why ); ?></p>
								<?php endif; ?>

								<?php
								/*
								 * The buy link is read from the linked review rather than
								 * duplicated onto the pick, so a price or URL is corrected in
								 * one place. e4c-compliance tags it on output.
								 */
								if ( $e4c_ref ) {
									$e4c_buy = e4c_field( 'e4c_buy_url', $e4c_ref );

									if ( $e4c_buy ) {
										e4c_button( (string) $e4c_buy, __( 'Where to buy it', 'e4c' ), 'secondary' );
									}
								}
								?>
							</div>

							<?php if ( $e4c_ref && has_post_thumbnail( $e4c_ref ) ) : ?>
								<a class="e4c-pick__media" href="<?php echo esc_url( (string) get_permalink( $e4c_ref ) ); ?>" tabindex="-1" aria-hidden="true">
									<?php echo get_the_post_thumbnail( $e4c_ref, 'e4c-thumb', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/related', 'reviews' ); ?>
	</article>
	<?php
endwhile;

get_footer();
