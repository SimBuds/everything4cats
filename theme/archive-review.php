<?php
/**
 * Review archive.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell e4c-section">
	<div class="e4c-section__head">
		<div>
			<h1 class="e4c-page-title"><?php esc_html_e( 'Reviews', 'e4c' ); ?></h1>
			<p class="e4c-page-lede"><?php echo esc_html( e4c_method_statement() ); ?></p>
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
		<p><?php esc_html_e( 'No reviews published yet.', 'e4c' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
