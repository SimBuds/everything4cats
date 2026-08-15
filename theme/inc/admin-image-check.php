<?php
/**
 * Warn in the editor when a featured image is too small for the theme's crops.
 *
 * WordPress never upscales. add_image_size( 'e4c-hero', 1600, 900, true ) is a
 * hard crop, so an image smaller than that in either dimension simply never has
 * the crop generated, and wp_get_attachment_image( $id, 'e4c-hero' ) silently
 * falls back to the full-size original at whatever shape it happens to be.
 *
 * That failure is invisible at the point it is caused and looks like a theme
 * bug at the point it is seen. On 2026-08-14 a 147x220 upload produced a hero
 * roughly 725px tall on a review and stretched a single card across the whole
 * archive. Both were diagnosed as layout faults twice before anyone looked at
 * the pixel dimensions. The CSS now pins the aspect ratios so the layout cannot
 * break again, but nothing can invent pixels: an undersized image is still
 * blurry, and the only place to catch it is here, at upload.
 *
 * Read from the registered sizes rather than hardcoded, so the numbers cannot
 * drift from inc/setup.php.
 *
 * @package Everything4Cats
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', 'e4c_featured_image_size_notice' );
/**
 * Notice on the post edit screen when the featured image is under-sized.
 */
function e4c_featured_image_size_notice(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->base || ! post_type_supports( $screen->post_type, 'thumbnail' ) ) {
		return;
	}

	$post_id = get_the_ID();
	$thumb   = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;

	if ( ! $thumb ) {
		return;
	}

	$meta = wp_get_attachment_metadata( $thumb );

	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return;
	}

	$short = array();

	foreach ( e4c_required_image_sizes() as $name => $dims ) {
		if ( $meta['width'] < $dims[0] || $meta['height'] < $dims[1] ) {
			$short[] = sprintf( '%s (%d x %d)', $name, $dims[0], $dims[1] );
		}
	}

	if ( ! $short ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p><p>%s</p></div>',
		esc_html__( 'Featured image is too small.', 'e4c' ),
		esc_html( sprintf(
			/* translators: 1: image width, 2: image height, 3: comma-separated list of crop names and sizes. */
			__( 'It is %1$d x %2$d, which is under the size needed for: %3$s.', 'e4c' ),
			(int) $meta['width'],
			(int) $meta['height'],
			implode( ', ', $short )
		) ),
		esc_html__( 'WordPress does not enlarge images, so those crops were never made and the original is used instead. It will look stretched or soft. Replace it with a larger file.', 'e4c' )
	);
}

/**
 * The theme's hard crops and the minimum source they need.
 *
 * Pulled from the registered sizes so this cannot drift from inc/setup.php.
 * Only cropped sizes are checked: an uncropped size scales down and is happy
 * with anything.
 *
 * @return array<string, array{0:int,1:int}>
 */
function e4c_required_image_sizes(): array {
	$out = array();

	foreach ( wp_get_additional_image_sizes() as $name => $size ) {
		if ( ! empty( $size['crop'] ) && 0 === strpos( $name, 'e4c-' ) ) {
			$out[ $name ] = array( (int) $size['width'], (int) $size['height'] );
		}
	}

	return $out;
}
