<?php
/**
 * Home page.
 *
 * The hero and the closing patch are block patterns so they are editable in
 * Gutenberg; the two feeds below are queries, because a list of the newest
 * reviews should not be a thing anyone maintains by hand.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="e4c-shell">

	<?php
	/*
	 * A front page assigned in Settings > Reading renders its own blocks, which
	 * is how the hero and patch patterns get edited. With no page assigned, or
	 * with one still empty, the theme falls back to the pattern markup so the
	 * top of the page is never blank.
	 *
	 * The condition tests the assigned page and its content rather than
	 * have_posts(), which was the original test and covered neither. On a static
	 * front page the loop always holds exactly one post, so have_posts() was
	 * permanently true and this fallback could never run: an empty front page
	 * rendered an empty top of page. On "Your latest posts" the loop is the blog
	 * loop instead, so the fallback appeared only while nothing was published,
	 * and the first post to go live would have replaced the hero with a stack of
	 * full post bodies.
	 *
	 * The homepage was therefore correct only by accident, and which accident
	 * applied depended on a Reading setting nobody had checked. Found on
	 * 2026-08-14 while answering how the homepage is edited from wp-admin.
	 */
	$e4c_front_id          = (int) get_option( 'page_on_front' );
	$e4c_front_has_content = $e4c_front_id
		&& '' !== trim( (string) get_post_field( 'post_content', $e4c_front_id ) );

	if ( $e4c_front_has_content ) :
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	else :
		/*
		 * wp_filter_content_tags() is the pass that adds srcset, sizes and the
		 * loading/fetchpriority hints to images. It is hooked to the_content,
		 * so the branch above gets it for free and this one does not: do_blocks()
		 * alone would serve the hero at a single resolution with no LCP hint.
		 *
		 * Applied here rather than by putting those attributes in the pattern,
		 * because the pattern is also insertable content and hardcoded
		 * render-time attributes make the image block invalid in the editor.
		 * See the note in patterns/home-hero.php.
		 */
		echo wp_filter_content_tags( do_blocks( '<!-- wp:pattern {"slug":"e4c/home-hero"} /-->' ) );
	endif;
	?>

	<?php
	$e4c_reviews = new WP_Query( array(
		'post_type'           => 'review',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_reviews->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-latest">
			<div class="e4c-section__head">
				<h2 id="e4c-latest"><?php esc_html_e( 'Recently tested', 'e4c' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'review' ) ); ?>"><?php esc_html_e( 'All reviews', 'e4c' ); ?></a>
			</div>

			<div class="e4c-grid e4c-grid--feature">
				<?php
				while ( $e4c_reviews->have_posts() ) :
					$e4c_reviews->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();

	$e4c_roundups = new WP_Query( array(
		'post_type'           => 'roundup',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_roundups->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-picks">
			<div class="e4c-section__head">
				<h2 id="e4c-picks"><?php esc_html_e( 'Our picks', 'e4c' ); ?></h2>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'roundup' ) ); ?>"><?php esc_html_e( 'All our picks', 'e4c' ); ?></a>
			</div>

			<div class="e4c-grid e4c-grid--feature">
				<?php
				while ( $e4c_roundups->have_posts() ) :
					$e4c_roundups->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>

	<?php
	/*
	 * Browse by category. Not a feed: the five terms of cat-category are the
	 * site's actual shape, and a reader who arrives without a product in mind
	 * needs a way in that is not a chronological list.
	 *
	 * hide_empty is true, so a term with nothing published does not advertise
	 * an empty archive. That means this section can render with two terms early
	 * on and five later, which is correct rather than something to pad out.
	 */
	$e4c_terms = get_terms( array(
		'taxonomy'   => 'cat-category',
		'hide_empty' => true,
	) );

	if ( $e4c_terms && ! is_wp_error( $e4c_terms ) ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-browse">
			<div class="e4c-section__head">
				<h2 id="e4c-browse"><?php esc_html_e( 'Browse by category', 'e4c' ); ?></h2>
			</div>

			<nav class="e4c-facets" aria-label="<?php esc_attr_e( 'Categories', 'e4c' ); ?>">
				<?php foreach ( $e4c_terms as $e4c_term ) : ?>
					<a class="e4c-facet" href="<?php echo esc_url( (string) get_term_link( $e4c_term ) ); ?>">
						<?php echo esc_html( $e4c_term->name ); ?>
						<span class="e4c-facet__count"><?php echo esc_html( (string) $e4c_term->count ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</section>
		<?php
	endif;

	/*
	 * Articles. The reviews and roundups above are the product coverage; this is
	 * everything else the site writes, and without it a published post has no
	 * route from the front page at all.
	 *
	 * Renders nothing while nothing is published, like both feeds above, so the
	 * homepage never shows an empty heading.
	 */
	$e4c_articles = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( $e4c_articles->have_posts() ) :
		?>
		<section class="e4c-section" aria-labelledby="e4c-articles">
			<div class="e4c-section__head">
				<h2 id="e4c-articles"><?php esc_html_e( 'Reading', 'e4c' ); ?></h2>
			</div>

			<div class="e4c-grid e4c-grid--feature">
				<?php
				while ( $e4c_articles->have_posts() ) :
					$e4c_articles->the_post();
					get_template_part( 'template-parts/card', 'post' );
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>

	<?php echo do_blocks( '<!-- wp:pattern {"slug":"e4c/newsletter-patch"} /-->' ); ?>
</div>
<?php
get_footer();
