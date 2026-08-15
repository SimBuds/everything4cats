<?php
/**
 * Template Name: Article with hero
 *
 * A full-width featured image above the title, then a single column of prose.
 * Use this where the image is the subject rather than an accompaniment.
 *
 * No rail, so everything the editor writes flows in one column at the body
 * measure. That makes it the right choice for pages built entirely out of
 * blocks and patterns.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'e4c-shell e4c-shell--narrow e4c-review' ); ?>>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="e4c-hero__figure">
				<?php e4c_hero_image( (int) get_post_thumbnail_id(), 'e4c-hero' ); ?>
			</figure>
		<?php endif; ?>

		<h1 class="e4c-page-title"><?php the_title(); ?></h1>

		<?php if ( has_excerpt() ) : ?>
			<p class="e4c-page-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>

		<div class="e4c-article"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
