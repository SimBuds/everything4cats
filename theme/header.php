<?php
/**
 * Document head, standing bar, and site header.
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="e4c-skip" href="#e4c-content"><?php esc_html_e( 'Skip to content', 'e4c' ); ?></a>

<p class="e4c-bar"><?php echo esc_html( e4c_method_statement() ); ?></p>

<header class="e4c-header">
	<div class="e4c-shell e4c-header__inner">
		<div class="e4c-brand">
			<?php
			/*
			 * The logo is in the header's critical path, so it loads eagerly at
			 * high priority. e4c_brand_logo() returns '' only when there is
			 * neither an uploaded logo nor the bundled file, which is when the
			 * site name as text is the correct answer rather than a gap.
			 */
			$e4c_logo = e4c_brand_logo( array(
				'fetchpriority' => 'high',
				'decoding'      => 'sync',
			) );

			if ( $e4c_logo ) :
				?>
				<a class="custom-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo $e4c_logo; // phpcs:ignore WordPress.Security.EscapeOutput -- attributes escaped in e4c_brand_logo(). ?>
				</a>
				<?php
			else :
				?>
				<a class="e4c-brand__text" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				<?php
			endif;
			?>
		</div>

		<?php
		/*
		 * The toggle only exists when there is a menu to toggle. Without one the
		 * panel below holds nothing but the two action buttons, and putting those
		 * behind a hamburger would hide the site's only calls to action to solve
		 * a problem that does not exist.
		 *
		 * It is a real <button>, not a link or a labelled checkbox, because it
		 * operates the page rather than navigating. aria-expanded is the state
		 * and assistive technology reads it; the icon is decorative and hidden.
		 *
		 * Rendered unconditionally rather than injected by script so its label
		 * and markup stay in PHP with the rest of the header. CSS keeps it
		 * display:none until the .e4c-js class says a script is running, so a
		 * reader without JavaScript never meets a button that cannot work.
		 */
		$e4c_has_primary = has_nav_menu( 'primary' );

		if ( $e4c_has_primary ) :
			?>
			<button type="button" class="e4c-btn e4c-btn--secondary e4c-navtoggle"
				aria-expanded="false" aria-controls="e4c-headerpanel">
				<span class="e4c-navtoggle__icon" aria-hidden="true"></span>
				<span class="e4c-navtoggle__label"><?php esc_html_e( 'Menu', 'e4c' ); ?></span>
			</button>
			<?php
		endif;
		?>

		<?php
		/*
		 * One wrapper around the nav AND the actions, because below the
		 * breakpoint both belong in the panel. On a 375px screen the row cannot
		 * hold a logo, a toggle and two buttons at once, so collapsing only the
		 * links would trade a two-row header for an overflowing one.
		 *
		 * display:contents above the breakpoint, so this element has no layout of
		 * its own and .e4c-nav and .e4c-actions stay direct flex items of
		 * .e4c-header__inner exactly as before. The wrapper costs nothing until
		 * it becomes the panel.
		 */
		?>
		<div id="e4c-headerpanel" class="e4c-headerpanel<?php echo $e4c_has_primary ? ' e4c-headerpanel--collapsible' : ''; ?>">
			<?php if ( $e4c_has_primary ) : ?>
				<nav class="e4c-nav" aria-label="<?php esc_attr_e( 'Primary', 'e4c' ); ?>">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 1,
						'items_wrap'     => '<ul>%3$s</ul>',
					) );
					?>
				</nav>
			<?php endif; ?>

			<div class="e4c-actions">
				<?php
				e4c_button( home_url( '/?s=' ), __( 'Search', 'e4c' ), 'secondary', array(
					'class'      => 'e4c-btn e4c-btn--secondary',
					'aria-label' => __( 'Search the site', 'e4c' ),
				) );

				$e4c_newsletter = get_page_by_path( 'newsletter' );
				if ( $e4c_newsletter ) {
					e4c_button( get_permalink( $e4c_newsletter ), __( 'Get the email', 'e4c' ), 'primary', array(
						'class' => 'e4c-btn e4c-btn--primary',
					) );
				}
				?>
			</div>
		</div>
	</div>
</header>

<main id="e4c-content" class="e4c-main">
