<?php
/**
 * Template Name: How we test
 *
 * The methodology page. This is the page the whole site's credibility rests on,
 * so it is a template rather than an ordinary page: the standing method
 * statement and the disclosure posture are rendered from code and cannot be
 * edited away by accident, while the substance underneath stays editable prose.
 *
 * Named as page-how-we-test.php AND carrying a Template Name header on purpose.
 * The filename makes WordPress pick it up automatically for a page with the
 * slug how-we-test, so a fresh install needs no admin step. The header also
 * puts it in the template dropdown, so a page at any other slug can still use
 * it. Neither alone covers both cases.
 *
 * No affiliate disclosure is printed here. plugins/e4c-compliance owns that
 * wording, and a second version of it on the page that explains the site's
 * ethics is exactly where two differently worded notices would do most damage.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'e4c-shell e4c-review' ); ?>>

		<div class="e4c-review__head">
			<div>
				<span class="e4c-tag"><?php esc_html_e( 'Methodology', 'e4c' ); ?></span>
				<h1><?php the_title(); ?></h1>
				<p class="e4c-review__dek"><?php echo esc_html( e4c_method_statement() ); ?></p>
			</div>
		</div>

		<section class="e4c-panel e4c-pledge" aria-labelledby="e4c-pledge-label">
			<span class="e4c-verdict__label" id="e4c-pledge-label"><?php esc_html_e( 'What we promise', 'e4c' ); ?></span>

			<ul class="e4c-list e4c-list--pros">
				<li>
					<span class="e4c-list__mark" aria-hidden="true">&plus;</span>
					<span><?php esc_html_e( 'We buy what we test. Nothing here was sent to us in exchange for coverage.', 'e4c' ); ?></span>
				</li>
				<li>
					<span class="e4c-list__mark" aria-hidden="true">&plus;</span>
					<span><?php esc_html_e( 'Every product spends real time in a real house with real cats before it is written about.', 'e4c' ); ?></span>
				</li>
				<li>
					<span class="e4c-list__mark" aria-hidden="true">&plus;</span>
					<span><?php esc_html_e( 'We say when something is not worth buying, and we say why.', 'e4c' ); ?></span>
				</li>
				<li>
					<span class="e4c-list__mark" aria-hidden="true">&plus;</span>
					<span><?php esc_html_e( 'Commissions never decide what gets covered or what we conclude about it.', 'e4c' ); ?></span>
				</li>
			</ul>
		</section>

		<div class="e4c-article"><?php the_content(); ?></div>

		<section class="e4c-panel e4c-pledge" aria-labelledby="e4c-vet-label">
			<span class="e4c-verdict__label" id="e4c-vet-label"><?php esc_html_e( 'Where we stop', 'e4c' ); ?></span>
			<p class="e4c-verdict__line">
				<?php esc_html_e( 'We review objects, not medicine. Anything touching illness, medication or a change of diet is a question for your vet, who has met your cat. We will tell you when a question is one of those.', 'e4c' ); ?>
			</p>
		</section>
	</article>
	<?php
endwhile;

get_footer();
