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
				<h2><?php esc_html_e( 'Everything4Cats', 'e4c' ); ?></h2>
				<p style="margin:0;color:var(--e4c-ink-muted);max-width:34ch;"><?php echo esc_html( e4c_method_statement() ); ?></p>
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
