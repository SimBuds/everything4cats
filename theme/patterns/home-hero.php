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

	<?php
	/*
	 * The hero image is the front page's Featured image, set in WordPress like
	 * any other, and emitted only when one exists.
	 *
	 * This block used to ship `<img alt="" width="1600" height="900" />` with no
	 * src. That is the ordinary idiom for a pattern being inserted in the
	 * editor, where it renders as an empty image block to fill. The problem is
	 * that front-page.php also renders this pattern straight to the front end
	 * when the front page has no content of its own, and there the placeholder
	 * became invalid markup holding open half the hero grid and showing
	 * nothing. Found during the theme QA on 2026-08-14.
	 *
	 * With no featured image the figure is absent entirely, auto-fit collapses
	 * the empty track, and the text takes the full width instead of sitting
	 * beside a hole.
	 *
	 * Reuses e4c_hero_image(), which already sets eager/sync/high: this is the
	 * one image on the site that is always above the fold.
	 */
	$e4c_hero_id = (int) get_post_thumbnail_id( (int) get_option( 'page_on_front' ) );
	if ( $e4c_hero_id ) :
		?>
	<!-- wp:image {"sizeSlug":"e4c-hero","className":"e4c-hero__figure"} -->
	<figure class="wp-block-image size-e4c-hero e4c-hero__figure"><?php e4c_hero_image( $e4c_hero_id, 'e4c-hero' ); ?></figure>
	<!-- /wp:image -->
		<?php
	endif;
	?>
</section>
<!-- /wp:group -->
