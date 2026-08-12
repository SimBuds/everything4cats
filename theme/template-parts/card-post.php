<?php
/**
 * One card in a grid. Used for reviews, roundups and guides alike: a style
 * variation is an input on this part, never a second near-identical file.
 */

defined( 'ABSPATH' ) || exit;

$e4c_kind  = get_post_type_object( get_post_type() );
$e4c_label = $e4c_kind ? $e4c_kind->labels->singular_name : '';
$e4c_dek   = e4c_field( 'e4c_dek' ) ?: get_the_excerpt();
?>
<article class="e4c-card">
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="e4c-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'e4c-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
		</a>
	<?php endif; ?>

	<?php if ( $e4c_label ) : ?>
		<span class="e4c-tag"><?php echo esc_html( $e4c_label ); ?></span>
	<?php endif; ?>

	<h3 class="e4c-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

	<?php if ( $e4c_dek ) : ?>
		<p style="margin:0;color:var(--e4c-ink-muted);"><?php echo esc_html( wp_trim_words( $e4c_dek, 26 ) ); ?></p>
	<?php endif; ?>

	<p class="e4c-card__meta">
		<?php
		printf(
			/* translators: 1: human readable date. */
			esc_html__( 'Tested %s', 'e4c' ),
			esc_html( get_the_date() )
		);
		?>
	</p>
</article>
