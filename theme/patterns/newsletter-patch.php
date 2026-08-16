<?php
/**
 * Title: Newsletter patch
 * Slug: e4c/newsletter-patch
 * Categories: e4c
 * Description: The closing sign-up block.
 * Keywords: newsletter, cta
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"e4c-patch","layout":{"type":"default"}} -->
<section class="wp-block-group e4c-patch">
	<!-- wp:heading {"fontSize":"heading-sub"} -->
	<h2 class="wp-block-heading has-heading-sub-font-size"><?php esc_html_e( 'One email a month. Only what held up.', 'e4c' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'What we tested, what we returned, and the one thing worth your money. Unsubscribe in a click.', 'e4c' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<?php // Core's classes only. See the note in home-hero.php. ?>
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/newsletter/"><?php esc_html_e( 'Get the email', 'e4c' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</section>
<!-- /wp:group -->
