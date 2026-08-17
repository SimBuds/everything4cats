<?php
/**
 * One card in a grid. Used for reviews, roundups and guides alike: a style
 * variation is an input on this part, never a second near-identical file.
 */

defined( 'ABSPATH' ) || exit;

$e4c_meta  = e4c_type_meta( (string) get_post_type() );
$e4c_label = $e4c_meta['label'];
$e4c_dek   = e4c_field( 'e4c_dek' ) ?: get_the_excerpt();
?>
<article class="e4c-card">
	<?php
	/*
	 * Every card has media, filled or not.
	 *
	 * The image used to be wrapped in has_post_thumbnail() alone, so a post
	 * without one produced a card that started at its tag instead. In a grid
	 * that is not a smaller card, it is a misaligned one: every title, dek and
	 * date in that row sits at a different height from its neighbours, and one
	 * imageless post makes the whole listing look broken rather than making one
	 * post look incomplete.
	 *
	 * The placeholder is not a missing-image icon. It holds the shape and stays
	 * quiet, because it is seen by readers and its job is to keep the grid
	 * even. The prompt to fix it belongs in the editor, where inc/admin-image-
	 * check.php already lives, not on the public page.
	 *
	 * It is a span rather than a link, aria-hidden, and outside the tab order:
	 * the title beside it already links to the post, and a second empty link to
	 * the same place is noise for anyone navigating by keyboard or screen
	 * reader.
	 */
	?>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="e4c-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'e4c-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
		</a>
	<?php else : ?>
		<span class="e4c-card__media e4c-card__media--empty" aria-hidden="true"></span>
	<?php endif; ?>

	<?php if ( $e4c_label ) : ?>
		<span class="e4c-tag"><?php echo esc_html( $e4c_label ); ?></span>
	<?php endif; ?>

	<h3 class="e4c-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

	<?php if ( $e4c_dek ) : ?>
		<p class="e4c-card__dek"><?php echo esc_html( wp_trim_words( $e4c_dek, 26 ) ); ?></p>
	<?php endif; ?>

	<p class="e4c-card__meta">
		<?php
		/*
		 * The verb comes from the post type, not from a hardcoded string. Every
		 * card used to read "Tested <date>", which put a testing claim on
		 * guides and roundups. On a site that stakes its credibility on the
		 * testing being real, saying it about an article nobody tested is the
		 * one kind of copy error that actually costs something.
		 */
		printf(
			esc_html( $e4c_meta['verb'] ),
			esc_html( 'roundup' === get_post_type() ? get_the_modified_date() : get_the_date() )
		);
		?>
	</p>
</article>
