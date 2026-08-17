<?php
/**
 * Template Name: Newsletter
 *
 * The subscribe page. Named page-newsletter.php and carrying a Template Name
 * header for the same reason as page-how-we-test.php: automatic on the expected
 * slug, still assignable anywhere else.
 *
 * DELIBERATELY NO FORM MARKUP OF ITS OWN.
 *
 * The list lives with the newsletter provider (beehiiv or Kit, per PLAN.md),
 * not in WordPress. Their embed is pasted into the page body in Gutenberg,
 * which is why the_content() sits where the form goes. Hardcoding a form here
 * would mean:
 *
 * - a POST handler this theme would have to own, plus spam handling,
 * - double opt-in implemented by hand, which is what CASL consent evidence
 *   actually rests on,
 * - unsubscribe and bounce handling, which is the provider's whole job.
 *
 * CASL matters here specifically. Canadian anti-spam law is opt-in rather than
 * opt-out, and the burden of proving consent sits with the sender. The
 * provider's confirmed opt-in record is that proof. A form posting to this site
 * would collect addresses with no defensible consent trail.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'e4c-shell e4c-shell--narrow e4c-review' ); ?>>

		<div class="e4c-review__head">
			<div>
				<span class="e4c-tag e4c-tag--second"><?php esc_html_e( 'Newsletter', 'e4c' ); ?></span>
				<h1><?php the_title(); ?></h1>
				<p class="e4c-review__dek">
					<?php esc_html_e( 'What we tested, what survived the cats, and what we would not buy again. No more than one email a week.', 'e4c' ); ?>
				</p>
			</div>
		</div>

		<?php
		/*
		 * The provider's embed block goes here, in the page body.
		 *
		 * The empty state used to render the newsletter-patch pattern, and that
		 * was circular: the patch is the module that sends people TO this page,
		 * so its heading repeated directly under the title and its "Get the
		 * email" button linked to /newsletter/, the page already being read. A
		 * call to action pointing at itself is worse than no call to action.
		 *
		 * What replaces it says the true thing instead. The pledge panel below
		 * still explains what subscribing means, so the page is never bare.
		 */
		if ( get_the_content() ) :
			?>
			<div class="e4c-article"><?php the_content(); ?></div>
			<?php
		else :
			?>
			<div class="e4c-article">
				<p><?php esc_html_e( 'The sign-up form is not connected yet. It will appear here as soon as the list is live.', 'e4c' ); ?></p>
			</div>
			<?php
		endif;
		?>

		<section class="e4c-panel e4c-pledge" aria-labelledby="e4c-list-label">
			<span class="e4c-verdict__label" id="e4c-list-label"><?php esc_html_e( 'What subscribing means', 'e4c' ); ?></span>

			<ul class="e4c-list e4c-list--pros">
				<li>
					<span><?php esc_html_e( 'You confirm by email before anything is sent. If you do not confirm, you hear nothing.', 'e4c' ); ?></span>
				</li>
				<li>
					<span><?php esc_html_e( 'Every email carries a one-click unsubscribe, and it works immediately.', 'e4c' ); ?></span>
				</li>
				<li>
					<span><?php esc_html_e( 'We never sell or share the list.', 'e4c' ); ?></span>
				</li>
			</ul>
		</section>
	</article>
	<?php
endwhile;

get_footer();
