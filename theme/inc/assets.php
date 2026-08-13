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
 *
 * Versioned by the file's own modification time rather than by
 * E4C_THEME_VERSION.
 *
 * The constant is bumped by hand, which means it is bumped when someone
 * remembers. Between 2026-08-12's theme deploy and this fix it stayed at 0.1.0
 * across every stylesheet change, so returning browsers kept serving a cached
 * copy while the server had a newer one. The symptom was a footer logo
 * rendering at full container width: the new .e4c-footer__logo rule existed on
 * disk and had never reached a browser, leaving the image with only the global
 * `img { max-width: 100% }`.
 *
 * That failure mode is nasty precisely because the fix looks wrong rather than
 * undelivered. filemtime() changes on every edit, so the cache key cannot drift
 * from the file again. plugins/e4c-compliance already versions disclosure.css
 * this way, so this matches an existing pattern rather than inventing one.
 */
function e4c_enqueue_assets(): void {
	$path = get_theme_file_path( 'style.css' );

	wp_enqueue_style(
		'e4c-style',
		get_stylesheet_uri(),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : E4C_THEME_VERSION
	);
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
