<?php
/**
 * Front-end and editor assets.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'e4c_fallback_site_icon', 5 );
/**
 * Emits a favicon link when no Site Icon has been set.
 *
 * WordPress only outputs icon tags once a Site Icon exists in Settings >
 * General, and it generates the several sizes browsers and mobile home screens
 * expect. That upload is still the right thing to do, and this does not replace
 * it: it covers the window before someone does, and a rebuilt host in which the
 * option has not been restored, so the tab is never the blank default sheet.
 *
 * Runs at priority 5, ahead of core's own wp_site_icon() at 99, and stands down
 * entirely the moment a real Site Icon exists so the two can never both emit.
 */
function e4c_fallback_site_icon(): void {
	if ( has_site_icon() ) {
		return;
	}

	$rel = 'assets/everything4cats-favicon.png';

	if ( ! file_exists( get_theme_file_path( $rel ) ) ) {
		return;
	}

	printf(
		'<link rel="icon" href="%1$s" sizes="512x512" type="image/png">' . "\n" .
		'<link rel="apple-touch-icon" href="%1$s">' . "\n",
		esc_url( get_theme_file_uri( $rel ) )
	);
}

add_action( 'wp_enqueue_scripts', 'e4c_enqueue_assets' );
/**
 * Loads the single stylesheet. Fonts are declared in theme.json so the editor
 * and the front end resolve one source of truth, with font-display: swap.
 */
function e4c_enqueue_assets(): void {
	wp_enqueue_style( 'e4c-style', get_stylesheet_uri(), array(), E4C_THEME_VERSION );
}

add_action( 'after_setup_theme', 'e4c_editor_styles' );
/**
 * Gives the block editor the same stylesheet, so prose written in Gutenberg
 * matches the published article rather than the editor's defaults.
 */
function e4c_editor_styles(): void {
	add_editor_style( 'style.css' );
}

add_filter( 'wp_resource_hints', 'e4c_no_external_font_hints', 10, 2 );
/**
 * Drops the fonts.gstatic.com hint core adds for Google-hosted fonts. Every
 * font here is self-hosted, so the preconnect would open a connection to a
 * host this site never contacts.
 *
 * @param array<string,array<int,string>> $hints    Hints by relation type.
 * @param string                          $relation Relation type.
 * @return array<string,array<int,string>>
 */
function e4c_no_external_font_hints( array $hints, string $relation ): array {
	if ( 'preconnect' !== $relation ) {
		return $hints;
	}
	$hints[ $relation ] = array_values( array_filter(
		$hints[ $relation ] ?? array(),
		static fn( $url ) => ! str_contains( is_array( $url ) ? ( $url['href'] ?? '' ) : $url, 'gstatic.com' )
	) );
	return $hints;
}
