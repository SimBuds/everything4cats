<?php
/**
 * Single review.
 *
 * The template renders from fields registered by plugins/e4c-content. It emits
 * no JSON-LD: plugins/e4c-compliance already outputs Article schema on every
 * singular view, and it reads the e4c-hero size registered in inc/setup.php.
 * It also emits no affiliate disclosure: the plugin injects .art-disclose above
 * the first paragraph of the_content when _e4c_post_affiliate is set, and a
 * second notice here would word one duty twice.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$e4c_dek     = e4c_field( 'e4c_dek' );
	$e4c_verdict = e4c_field( 'e4c_verdict' );
	$e4c_price   = e4c_field( 'e4c_price' );
	$e4c_pros    = e4c_field( 'e4c_pros' );
	$e4c_cons    = e4c_field( 'e4c_cons' );
	$e4c_specs   = e4c_field( 'e4c_specs' );
	$e4c_buy_url = e4c_field( 'e4c_buy_url' );
	$e4c_tested  = e4c_field( 'e4c_tested_for' );
	?>
	<article <?php post_class( 'e4c-shell e4c-review' ); ?>>

		<div class="e4c-review__head">
			<div>
				<span class="e4c-tag"><?php esc_html_e( 'Review', 'e4c' ); ?></span>
				<h1><?php the_title(); ?></h1>

				<?php if ( $e4c_dek ) : ?>
					<p class="e4c-review__dek"><?php echo esc_html( $e4c_dek ); ?></p>
				<?php endif; ?>

				<p class="e4c-review__byline">
					<?php
					// See the note in single.php: no name means no "By".
					if ( get_the_author() ) {
						printf(
							/* translators: 1: author name, 2: date the review was published or updated. */
							esc_html__( 'By %1$s, tested %2$s', 'e4c' ),
							esc_html( get_the_author() ),
							esc_html( get_the_modified_date() )
						);
					} else {
						/* translators: %s: date the review was published or updated. */
						printf( esc_html__( 'Tested %s', 'e4c' ), esc_html( get_the_modified_date() ) );
					}

					if ( $e4c_tested ) {
						printf(
							/* translators: %s: how long the product was in use. */
							' &middot; ' . esc_html__( 'In use for %s', 'e4c' ),
							esc_html( $e4c_tested )
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

		<?php if ( $e4c_verdict || $e4c_price || $e4c_buy_url ) : ?>
			<section class="e4c-panel e4c-verdict" aria-labelledby="e4c-verdict-label">
				<span class="e4c-verdict__label" id="e4c-verdict-label"><?php esc_html_e( 'The verdict', 'e4c' ); ?></span>

				<?php if ( $e4c_verdict ) : ?>
					<p class="e4c-verdict__line"><?php echo esc_html( $e4c_verdict ); ?></p>
				<?php endif; ?>

				<?php if ( $e4c_price || $e4c_buy_url ) : ?>
					<div class="e4c-buy">
						<?php if ( $e4c_price ) : ?>
							<span class="e4c-verdict__price"><?php echo esc_html( $e4c_price ); ?></span>
						<?php endif; ?>

						<?php
						/*
						 * The href is printed raw. plugins/e4c-compliance filters outbound
						 * commercial links and adds rel="sponsored nofollow" plus its own
						 * tagging, so adding rel here would either duplicate or fight it.
						 */
						if ( $e4c_buy_url ) {
							e4c_button( $e4c_buy_url, __( 'Where to buy it', 'e4c' ), 'primary' );
						}
						?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>


		<div class="e4c-review__body">

			<div class="e4c-article"><?php the_content(); ?></div>

			<?php if ( $e4c_pros || $e4c_cons ) : ?>
				<aside class="e4c-cols">
					<?php if ( $e4c_pros ) : ?>
						<div>
							<h2 class="e4c-section-title"><?php esc_html_e( 'What works', 'e4c' ); ?></h2>
							<ul class="e4c-list e4c-list--pros">
								<?php foreach ( (array) $e4c_pros as $e4c_row ) : ?>
									<?php $e4c_text = is_array( $e4c_row ) ? ( $e4c_row['text'] ?? '' ) : $e4c_row; ?>
									<?php if ( $e4c_text ) : ?>
										<li><?php echo esc_html( $e4c_text ); ?></li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( $e4c_cons ) : ?>
						<div>
							<h2 class="e4c-section-title"><?php esc_html_e( 'What does not', 'e4c' ); ?></h2>
							<ul class="e4c-list e4c-list--cons">
								<?php foreach ( (array) $e4c_cons as $e4c_row ) : ?>
									<?php $e4c_text = is_array( $e4c_row ) ? ( $e4c_row['text'] ?? '' ) : $e4c_row; ?>
									<?php if ( $e4c_text ) : ?>
										<li><?php echo esc_html( $e4c_text ); ?></li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Test photos. Deliberately placed after the article and before the
		 * specs: the prose makes the claims, these show them, and the spec
		 * table is reference material nobody reads in order.
		 *
		 * Full shell width rather than the body measure, because the point of
		 * these is that they are photographs and a 62ch column would render
		 * them as thumbnails. This is the widest thing on a review page.
		 *
		 * The caption doubles as alt text. Two fields would mean two chances to
		 * leave one empty, and a caption that describes the photo is exactly
		 * what alt text needs to say. Where a caption is missing the image is
		 * still rendered with alt="" rather than skipped, which marks it
		 * decorative rather than lying about its content.
		 */
		$e4c_gallery = e4c_field( 'e4c_gallery' );

		if ( $e4c_gallery ) :
			$e4c_shots = array();

			foreach ( (array) $e4c_gallery as $e4c_row ) {
				$e4c_img = is_array( $e4c_row ) ? ( $e4c_row['image'] ?? 0 ) : $e4c_row;

				// An image field set to return an array, or a row left behind
				// empty in the editor. Normalised rather than assumed, for the
				// same reason single-roundup.php normalises its post object.
				if ( is_array( $e4c_img ) ) {
					$e4c_img = $e4c_img['ID'] ?? 0;
				}

				$e4c_img = (int) $e4c_img;

				if ( $e4c_img ) {
					$e4c_shots[] = array(
						'id'      => $e4c_img,
						'caption' => is_array( $e4c_row ) ? (string) ( $e4c_row['caption'] ?? '' ) : '',
					);
				}
			}
			?>
			<?php if ( $e4c_shots ) : ?>
				<section class="e4c-shots" aria-labelledby="e4c-shots-label">
					<h2 class="e4c-section-title" id="e4c-shots-label"><?php esc_html_e( 'In the house', 'e4c' ); ?></h2>

					<ul class="e4c-shots__grid">
						<?php foreach ( $e4c_shots as $e4c_shot ) : ?>
							<li class="e4c-shot">
								<figure>
									<?php
									echo wp_get_attachment_image(
										$e4c_shot['id'],
										'e4c-card',
										false,
										array(
											'loading' => 'lazy',
											'alt'     => $e4c_shot['caption'],
										)
									);
									?>
									<?php if ( $e4c_shot['caption'] ) : ?>
										<figcaption><?php echo esc_html( $e4c_shot['caption'] ); ?></figcaption>
									<?php endif; ?>
								</figure>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $e4c_specs ) : ?>
			<section class="e4c-after-article">
				<h2 class="e4c-section-title"><?php esc_html_e( 'Specifications', 'e4c' ); ?></h2>
				<table class="e4c-specs">
					<tbody>
						<?php foreach ( (array) $e4c_specs as $e4c_row ) : ?>
							<?php
							$e4c_key   = is_array( $e4c_row ) ? ( $e4c_row['label'] ?? '' ) : '';
							$e4c_value = is_array( $e4c_row ) ? ( $e4c_row['value'] ?? '' ) : '';
							if ( ! $e4c_key && ! $e4c_value ) {
								continue;
							}
							?>
							<tr>
								<th scope="row"><?php echo esc_html( $e4c_key ); ?></th>
								<td><?php echo esc_html( $e4c_value ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/related', 'reviews' ); ?>
	</article>
	<?php
endwhile;

get_footer();
