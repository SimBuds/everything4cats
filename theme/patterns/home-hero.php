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

		<?php
		/*
		 * The anchors carry ONLY the classes core's button block generates.
		 *
		 * They used to also carry `e4c-btn e4c-btn--primary`, which is how the
		 * theme's control base was reached. That markup renders correctly and
		 * is invalid as a block: the button block computes the anchor's class
		 * list from its attributes, so save() regenerates it without the two
		 * theme classes, the editor compares the two and reports "Block
		 * contains unexpected or invalid content" with an Attempt recovery
		 * button. Recovery then silently strips the styling.
		 *
		 * A pattern is editable content, so its markup has to be exactly what
		 * the block would serialise. The styling moved to style.css, which
		 * targets .wp-block-button__link directly. Found 2026-08-16 on the
		 * first real insertion of this pattern in the editor.
		 */
		?>
		<!-- wp:buttons {"className":"e4c-hero__actions"} -->
		<div class="wp-block-buttons e4c-hero__actions">
			<!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/reviews/"><?php esc_html_e( 'Read the reviews', 'e4c' ); ?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/how-we-test/"><?php esc_html_e( 'How we test', 'e4c' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->

	<?php
	/*
	 * THE HERO IMAGE IS AN IMAGE BLOCK, NOT THE FEATURED IMAGE.
	 *
	 * It was the Featured image until 2026-08-16, resolved here in PHP and
	 * emitted only when one existed. That reads as reasonable and is unusable,
	 * because a pattern's PHP runs ONCE, at the moment the author clicks it in
	 * the inserter. Whatever branch was true then is frozen into the page as
	 * static blocks. Insert the pattern before setting a Featured image, which
	 * is the obvious order, and the page is saved with no image block at all
	 * and no way to add one: the editor shows no image, and setting a Featured
	 * image afterwards has nothing left to read it. Reported as "no option to
	 * add image in homepage hero".
	 *
	 * An always-present empty image block is the ordinary pattern idiom and it
	 * is what the editor needs: the author sees an upload placeholder, clicks
	 * it, and picks a photo. No ordering to get right and nothing hidden.
	 *
	 * This was tried once before and reverted on 2026-08-14 because the SAME
	 * pattern is rendered straight to the front end by front-page.php when the
	 * front page has no content, and there a src-less <img> held open half the
	 * hero grid and showed nothing. That no longer happens, and it is worth
	 * being precise about why rather than trusting the revert: core's
	 * render_block_core_image() returns an empty string for any image block
	 * whose img has no src, so the placeholder renders as nothing at all on
	 * both the fallback and the_content paths. Verified on WP 7.0.4, not
	 * assumed. auto-fit then collapses the empty track and the text takes the
	 * full width, which is the behaviour the old PHP branch was hand-rolling.
	 *
	 * The Featured image still matters, for the job it is actually for: Rank
	 * Math and the OG tags use it for social cards. It is simply no longer
	 * wired to the hero, so there is one image block to fill and one place to
	 * look when it is wrong.
	 */
	?>
	<!-- wp:image {"sizeSlug":"e4c-hero","className":"e4c-hero__figure"} -->
	<figure class="wp-block-image size-e4c-hero e4c-hero__figure"><img alt=""/></figure>
	<!-- /wp:image -->
</section>
<!-- /wp:group -->
