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
			 * Three tiers, in order of preference.
			 *
			 * 1. A logo uploaded through Customizer > Site Identity. Preferred,
			 *    because WordPress then owns the srcset and the sizes.
			 * 2. The logo bundled in the theme, when nothing has been uploaded.
			 *    Same reasoning as front-page.php falling back to the hero
			 *    pattern: a fresh install or a rebuilt host should carry the
			 *    brand immediately rather than showing plain text until someone
			 *    remembers an admin step.
			 * 3. The site name as text, if the bundled file is ever removed.
			 *
			 * Width and height are hardcoded from the file's real dimensions
			 * (2137x498) so the header reserves its space before the image
			 * loads. Without them the nav jumps on first paint, which is a
			 * layout-shift cost paid on every uncached visit.
			 */
			$e4c_logo_rel  = 'assets/everything4cats-logo.png';
			$e4c_logo_path = get_theme_file_path( $e4c_logo_rel );

			if ( has_custom_logo() ) :
				the_custom_logo();
			elseif ( file_exists( $e4c_logo_path ) ) :
				?>
				<a class="custom-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<img
						class="custom-logo"
						src="<?php echo esc_url( get_theme_file_uri( $e4c_logo_rel ) ); ?>"
						width="2137" height="498"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
						fetchpriority="high" decoding="sync"
					>
				</a>
				<?php
			else :
				?>
				<a class="e4c-brand__text" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				<?php
			endif;
			?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
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
				'class'      => 'e4c-btn e4c-btn--secondary e4c-btn--sm',
				'aria-label' => __( 'Search the site', 'e4c' ),
			) );

			$e4c_newsletter = get_page_by_path( 'newsletter' );
			if ( $e4c_newsletter ) {
				e4c_button( get_permalink( $e4c_newsletter ), __( 'Get the email', 'e4c' ), 'primary', array(
					'class' => 'e4c-btn e4c-btn--primary e4c-btn--sm',
				) );
			}
			?>
		</div>
	</div>
</header>

<main id="e4c-content" class="e4c-main">
