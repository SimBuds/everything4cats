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
