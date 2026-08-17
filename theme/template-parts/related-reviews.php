<?php
/**
 * Up to three more reviews, newest first, excluding the current one.
 */

defined( 'ABSPATH' ) || exit;

$e4c_related = new WP_Query( array(
	'post_type'           => 'review',
	'posts_per_page'      => 3,
	'post__not_in'        => array( get_the_ID() ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );

if ( $e4c_related->have_posts() ) :
	?>
	<section class="e4c-section" aria-labelledby="e4c-related">
		<div class="e4c-section__head">
			<h2 id="e4c-related"><?php esc_html_e( 'More things we tested', 'e4c' ); ?></h2>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'review' ) ); ?>"><?php esc_html_e( 'All reviews', 'e4c' ); ?></a>
		</div>

		<div class="e4c-grid e4c-grid--feature">
			<?php
			while ( $e4c_related->have_posts() ) :
				$e4c_related->the_post();
				get_template_part( 'template-parts/card', 'post' );
			endwhile;
			?>
		</div>
	</section>
	<?php
endif;

wp_reset_postdata();
