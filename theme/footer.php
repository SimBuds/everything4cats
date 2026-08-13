<?php
/**
 * Site footer.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="e4c-footer">
	<div class="e4c-shell">
		<div class="e4c-footer__cols">
			<div>
				<?php
				/*
				 * The logo sits inside the heading rather than replacing it, so
				 * the footer keeps its heading structure and the image's alt
				 * text supplies the accessible name. Falls back to the text
				 * heading when no logo is available anywhere.
				 *
				 * Not wrapped in a link, deliberately. The header logo already
				 * links home, and a second identical link to the same place is
				 * one more stop for anyone tabbing through the footer without
				 * being a second destination.
				 *
				 * Lazy, and no fetchpriority: this is below the fold on every
				 * page, so competing with the hero for early bandwidth would be
				 * a real cost for no gain.
				 */
				$e4c_footer_logo = e4c_brand_logo( array(
					'class'   => 'custom-logo e4c-footer__logo',
					'loading' => 'lazy',
					'decoding' => 'async',
				) );
				?>
				<h2 class="e4c-footer__brand">
					<?php
					if ( $e4c_footer_logo ) {
						echo $e4c_footer_logo; // phpcs:ignore WordPress.Security.EscapeOutput -- attributes escaped in e4c_brand_logo().
					} else {
						echo esc_html( get_bloginfo( 'name' ) );
					}
					?>
				</h2>
				<p class="e4c-footer__statement"><?php echo esc_html( e4c_method_statement() ); ?></p>
			</div>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<div>
					<h2><?php esc_html_e( 'Read', 'e4c' ); ?></h2>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'items_wrap'     => '<ul>%3$s</ul>',
					) );
					?>
				</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<div>
					<h2><?php esc_html_e( 'Legal', 'e4c' ); ?></h2>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'legal',
						'container'      => false,
						'depth'          => 1,
						'items_wrap'     => '<ul>%3$s</ul>',
					) );
					?>
				</div>
			<?php endif; ?>
		</div>

		<p class="e4c-footer__legal">
			<?php
			printf(
				/* translators: 1: current year, 2: site name. */
				esc_html__( '%1$s %2$s. Independently tested in a real house with real cats.', 'e4c' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
