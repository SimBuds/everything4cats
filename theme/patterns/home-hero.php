<?php
/**
 * Title: Home hero
 * Slug: e4c/home-hero
 * Categories: e4c
 * Description: The headline, the standing promise and the two entry points.
 * Keywords: hero, home
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"e4c-hero","layout":{"type":"default"}} -->
<section class="wp-block-group e4c-hero">
	<!-- wp:group {"layout":{"type":"default"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph {"className":"e4c-tag"} -->
		<p class="e4c-tag"><?php esc_html_e( 'Independently tested', 'e4c' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"fontSize":"heading-hero"} -->
		<h1 class="wp-block-heading has-heading-hero-font-size"><?php esc_html_e( 'Cat gear that survived a real house.', 'e4c' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"e4c-hero__lede"} -->
		<p class="e4c-hero__lede"><?php esc_html_e( 'We buy every product ourselves, live with it for weeks, and write down what broke. No sponsored placements, no borrowed samples, no scores invented to fill a table.', 'e4c' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"className":"e4c-hero__actions"} -->
		<div class="wp-block-buttons e4c-hero__actions">
			<!-- wp:button {"className":"is-style-fill"} -->
			<div class="wp-block-button is-style-fill"><a class="wp-block-button__link e4c-btn e4c-btn--primary wp-element-button" href="/reviews/"><?php esc_html_e( 'Read the reviews', 'e4c' ); ?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link e4c-btn e4c-btn--secondary wp-element-button" href="/how-we-test/"><?php esc_html_e( 'How we test', 'e4c' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->

	<!-- wp:image {"sizeSlug":"e4c-hero","className":"e4c-hero__figure"} -->
	<figure class="wp-block-image size-e4c-hero e4c-hero__figure"><img alt="" width="1600" height="900" /></figure>
	<!-- /wp:image -->
</section>
<!-- /wp:group -->
