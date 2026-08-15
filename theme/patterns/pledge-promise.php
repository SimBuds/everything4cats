<?php
/**
 * Title: Promise panel
 * Slug: e4c/pledge-promise
 * Categories: e4c
 * Description: The standing editorial promise, as an editable panel.
 * Keywords: promise, pledge, ethics, methodology
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"e4c-panel e4c-pledge","layout":{"type":"default"}} -->
<section class="wp-block-group e4c-panel e4c-pledge">
	<!-- wp:paragraph {"className":"e4c-verdict__label"} -->
	<p class="e4c-verdict__label"><?php esc_html_e( 'What we promise', 'e4c' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:list {"className":"e4c-list e4c-list--pros"} -->
	<ul class="wp-block-list e4c-list e4c-list--pros">
		<!-- wp:list-item -->
		<li><?php esc_html_e( 'We buy what we test. Nothing here was sent to us in exchange for coverage.', 'e4c' ); ?></li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li><?php esc_html_e( 'Every product spends real time in a real house with real cats before it is written about.', 'e4c' ); ?></li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li><?php esc_html_e( 'We say when something is not worth buying, and we say why.', 'e4c' ); ?></li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li><?php esc_html_e( 'Commissions never decide what gets covered or what we conclude about it.', 'e4c' ); ?></li>
		<!-- /wp:list-item -->
	</ul>
	<!-- /wp:list -->
</section>
<!-- /wp:group -->
