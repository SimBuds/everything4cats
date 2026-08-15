<?php
/**
 * Template Name: Article with side rail
 *
 * Prose on the left at a readable measure, the featured image in a rail on the
 * right. The rail is template furniture: page content is a single flow and
 * lands in the prose column, so anything meant for the rail has to come from
 * the template rather than the editor.
 *
 * Use this for pages carrying a standing image next to a long read. Panels such
 * as the promise and vet notes are patterns now, so they are inserted into the
 * content and appear in the prose column, not the rail.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'e4c-shell e4c-shell--narrow e4c-review e4c-artgrid' ); ?>>

		<h1><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="e4c-review__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<div class="e4c-article"><?php the_content(); ?></div>

		<?php if ( has_post_thumbnail() ) : ?>
			<aside class="e4c-cols">
				<figure class="e4c-hero__figure">
					<?php e4c_hero_image( (int) get_post_thumbnail_id(), 'e4c-hero' ); ?>
				</figure>
			</aside>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
