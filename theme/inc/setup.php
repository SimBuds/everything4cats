<?php
/**
 * Theme supports, menus, and image sizes.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'e4c_setup' );
/**
 * Registers theme support and the image sizes the templates and the compliance
 * plugin request.
 */
function e4c_setup(): void {
	load_theme_textdomain( 'e4c', get_theme_file_path( 'languages' ) );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-logo', array(
		'height'      => 84,
		'width'       => 320,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	/*
	 * e4c-hero is requested by plugins/e4c-compliance for the Article schema
	 * image. Unregistered, WordPress falls back to the full-size upload and the
	 * structured data advertises a camera-resolution file.
	 */
	add_image_size( 'e4c-hero', 1600, 900, true );
	add_image_size( 'e4c-card', 800, 600, true );
	add_image_size( 'e4c-thumb', 320, 320, true );

	register_nav_menus( array(
		'primary' => __( 'Primary navigation', 'e4c' ),
		'footer'  => __( 'Footer links', 'e4c' ),
		'legal'   => __( 'Legal links', 'e4c' ),
	) );
}

add_filter( 'wp_image_editor_default_to_srgb', '__return_true' );

add_filter( 'image_size_names_choose', 'e4c_image_size_names' );
/**
 * Exposes the theme's crops in the editor's size picker.
 *
 * @param array<string,string> $sizes Registered choices.
 * @return array<string,string>
 */
function e4c_image_size_names( array $sizes ): array {
	$sizes['e4c-hero'] = __( 'Hero (16:9)', 'e4c' );
	$sizes['e4c-card'] = __( 'Card (4:3)', 'e4c' );
	return $sizes;
}
