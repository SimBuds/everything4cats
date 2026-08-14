<?php
/**
 * The generic page template.
 *
 * Deliberately plain: header, title, content, footer, with no furniture of its
 * own. Pages that want furniture get their own template, as How we test and
 * Newsletter do.
 *
 * Added 2026-08-14 to close a bug rather than to add a feature. The theme had
 * no page.php at all, so WordPress walked the hierarchy past page-{slug},
 * page-{id}, page and singular, and landed on index.php. index.php is an
 * archive template: it loops results through template-parts/card-post.php,
 * which renders esc_html( wp_trim_words( $dek, 26 ) ). A page with no bespoke
 * template was therefore published as a 26-word card with every tag stripped.
 *
 * The Privacy Policy and the Cookie Policy were both in that state, with the
 * consent banner linking to both. The gap stayed hidden because every page that
 * existed until then was matched by slug, so the fallback path had never been
 * exercised by a real page.
 *
 * Worth knowing for the next template: a missing template does not error. It
 * silently renders through a less specific one, and the failure looks like a
 * content problem rather than a theme problem. Two rounds of debugging here
 * blamed the block editor's paste handling before the stored post content was
 * checked and found to be perfect.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'e4c-shell' ); ?>>

		<h1 class="e4c-page-title"><?php the_title(); ?></h1>

		<?php
		/*
		 * No get_the_content() guard, unlike page-newsletter.php. That template
		 * falls back to a pattern because an empty newsletter page would be a
		 * blank promise of a signup form. Here an empty page is simply an empty
		 * page, and rendering the title alone is the honest result.
		 */
		?>
		<div class="e4c-article"><?php the_content(); ?></div>

	</article>
	<?php
endwhile;

get_footer();
