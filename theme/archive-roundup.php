<?php
/**
 * Roundup archive.
 *
 * Added 2026-08-14. Until then /best/ fell through to index.php, which is the
 * general fallback and derives its heading from the_archive_title(). That
 * produced "Archives: Roundups" with no standing line under it, on a page
 * carrying one of the four primary nav links.
 *
 * index.php's docblock argues against a template per case, and it is right for
 * tag, author and date archives, which are incidental. It is wrong here for the
 * same reason archive-review.php exists: /best/ is a destination someone
 * chooses from the nav, and a roundup is the format where the site's ranking is
 * doing the most work. The page that lists them should say what a rank means
 * before a reader trusts one.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell e4c-section">
	<div class="e4c-section__head">
		<div>
			<h1 class="e4c-page-title"><?php esc_html_e( 'Best', 'e4c' ); ?></h1>
			<p class="e4c-page-lede"><?php esc_html_e( 'Every product in these lists was bought and used. Rank comes from testing, never from what a link pays.', 'e4c' ); ?></p>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="e4c-grid e4c-grid--feature">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/card', 'post' );
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination( array(
			'class'     => 'e4c-pagination',
			'mid_size'  => 1,
			'prev_text' => __( 'Newer', 'e4c' ),
			'next_text' => __( 'Older', 'e4c' ),
		) );
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'No roundups published yet.', 'e4c' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
