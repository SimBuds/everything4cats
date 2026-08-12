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
 * The site's standing method statement for the top bar. This is not the paid
 * link disclosure: plugins/e4c-compliance owns that, and duplicating it here
 * would put two differently worded notices on the same page.
 */
function e4c_method_statement(): string {
	/* translators: the standing bar above the site header. */
	return __( 'Everything here lived in a real house with real cats. We buy what we test, and we tell you when not to buy.', 'e4c' );
}
