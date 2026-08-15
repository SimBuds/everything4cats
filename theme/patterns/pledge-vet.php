<?php
/**
 * Title: Where we stop panel
 * Slug: e4c/pledge-vet
 * Categories: e4c
 * Description: The boundary between product reviewing and veterinary advice.
 * Keywords: vet, medical, boundary, ymyl
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"e4c-panel e4c-pledge","layout":{"type":"default"}} -->
<section class="wp-block-group e4c-panel e4c-pledge">
	<!-- wp:paragraph {"className":"e4c-verdict__label"} -->
	<p class="e4c-verdict__label"><?php esc_html_e( 'Where we stop', 'e4c' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph {"className":"e4c-verdict__line"} -->
	<p class="e4c-verdict__line"><?php esc_html_e( 'We review objects, not medicine. Anything touching illness, medication or a change of diet is a question for your vet, who has met your cat. We will tell you when a question is one of those.', 'e4c' ); ?></p>
	<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
