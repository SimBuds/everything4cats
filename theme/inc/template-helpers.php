<?php
/**
 * Small presentation helpers shared by the templates.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads a field through ACF when the plugin is active, and falls back to raw
 * post meta so a template never renders empty just because ACF is off.
 *
 * @param string   $key     Field key, without the leading underscore.
 * @param int|null $post_id Post to read. Defaults to the current post.
 * @return mixed
 */
function e4c_field( string $key, ?int $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();

	if ( function_exists( 'get_field' ) ) {
		return get_field( $key, $post_id );
	}

	return get_post_meta( $post_id, $key, true );
}

/**
 * Echoes the hero image with explicit dimensions, eager loading and high
 * fetch priority. Core marks the first in-content image this way only
 * sometimes, and a hero that shifts is a Core Web Vitals cost decided here.
 *
 * @param int    $attachment_id Attachment to render.
 * @param string $size          Registered image size.
 * @param string $class         Wrapper class for the img element.
 */
function e4c_hero_image( int $attachment_id, string $size = 'e4c-hero', string $class = '' ): void {
	if ( ! $attachment_id ) {
		return;
	}

	echo wp_get_attachment_image( $attachment_id, $size, false, array(
		'class'         => $class,
		'loading'       => 'eager',
		'decoding'      => 'sync',
		'fetchpriority' => 'high',
	) );
}

/**
 * Renders one button. Buttons are anchors here because every one of them
 * navigates.
 *
 * @param string $url     Destination.
 * @param string $label   Visible label.
 * @param string $variant primary|secondary.
 * @param array<string,string> $attrs Extra attributes, escaped on output.
 */
function e4c_button( string $url, string $label, string $variant = 'primary', array $attrs = array() ): void {
	if ( ! $url || ! $label ) {
		return;
	}

	$extra = '';
	foreach ( $attrs as $name => $value ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	printf(
		'<a class="e4c-btn e4c-btn--%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( $variant ),
		esc_url( $url ),
		$extra, // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_attr above.
		esc_html( $label )
	);
}

/**
 * The brand logo markup, in three tiers.
 *
 * 1. A logo uploaded through Customizer > Site Identity. Preferred, because
 *    WordPress owns the srcset and a phone then downloads a resized copy
 *    rather than the full-width original.
 * 2. The logo bundled at theme/assets/. Same reasoning as front-page.php
 *    falling back to the hero pattern: a fresh install or a rebuilt host
 *    should carry the brand immediately rather than showing plain text until
 *    someone remembers an admin step.
 * 3. An empty string, leaving the caller to render the site name as text.
 *
 * Returns the image only, never a link, because the header wraps it in one and
 * the footer deliberately does not. Two callers with different needs is what
 * makes this a function with arguments rather than two copies of the tiering.
 *
 * @param array<string,string> $attrs Extra img attributes, escaped on output.
 * @return string Image markup, or '' when no logo is available at all.
 */
function e4c_brand_logo( array $attrs = array() ): string {
	$attrs = array_merge(
		array(
			'class' => 'custom-logo',
			'alt'   => get_bloginfo( 'name' ),

			/*
			 * WordPress defaults an uploaded logo's sizes attribute to
			 * "(max-width: 2137px) 100vw, 2137px", which claims the image fills
			 * the viewport. It never does: style.css caps it at 42px tall in the
			 * header and 44px in the footer, and at the source ratio of roughly
			 * 4.3:1 that is about 190px wide at most.
			 *
			 * Left at the default, a 1440px screen selects the 1536w file,
			 * roughly 193 KB, to paint 190px. That is barely better than the
			 * unresized original the upload was meant to replace, which made the
			 * whole srcset ladder decorative.
			 *
			 * Browsers multiply this by device pixel ratio when choosing, so
			 * 200px asks for 200w on a standard display and 400w on a 2x one,
			 * landing on the 300w and 768w files respectively.
			 *
			 * Revisit if the logo's aspect ratio changes substantially, since
			 * this width is derived from the height cap in style.css and that
			 * ratio.
			 */
			'sizes' => '200px',
		),
		$attrs
	);

	$logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		return (string) wp_get_attachment_image( $logo_id, 'full', false, $attrs );
	}

	$rel = 'assets/everything4cats-logo.png';

	if ( ! file_exists( get_theme_file_path( $rel ) ) ) {
		return '';
	}

	/*
	 * Width and height are the bundled file's real dimensions. Without them the
	 * surrounding layout reflows on first paint, which is a layout-shift cost
	 * paid on every uncached visit. The uploaded path above gets these from
	 * WordPress automatically, which is one more reason to prefer it.
	 */
	$markup = sprintf(
		'<img src="%s" width="2137" height="498"',
		esc_url( get_theme_file_uri( $rel ) )
	);

	foreach ( $attrs as $name => $value ) {
		$markup .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	return $markup . '>';
}

/**
 * The site's standing method statement for the top bar. This is not the paid
 * link disclosure: plugins/e4c-compliance owns that, and duplicating it here
 * would put two differently worded notices on the same page.
 */
function e4c_method_statement(): string {
	/* translators: the standing bar above the site header. */
	return __( 'Everything here lived in a real house with real cats. We buy what we test, and we tell you when not to buy.', 'e4c' );
}
