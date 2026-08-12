<?php
/**
 * Post types.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'e4c_content_register_post_types' );
/**
 * Registers the review and roundup types.
 *
 * show_in_rest is on for both: the article body is written in Gutenberg, and
 * the theme's theme.json styles it there.
 */
function e4c_content_register_post_types(): void {
	register_post_type( 'review', array(
		'labels'        => array(
			'name'               => __( 'Reviews', 'e4c-content' ),
			'singular_name'      => __( 'Review', 'e4c-content' ),
			'add_new_item'       => __( 'Add review', 'e4c-content' ),
			'edit_item'          => __( 'Edit review', 'e4c-content' ),
			'search_items'       => __( 'Search reviews', 'e4c-content' ),
			'not_found'          => __( 'No reviews yet', 'e4c-content' ),
			'all_items'          => __( 'All reviews', 'e4c-content' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-star-half',
		'menu_position' => 5,
		'rewrite'       => array( 'slug' => 'reviews', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
		'show_in_rest'  => true,
		'taxonomies'    => array( 'cat-category', 'post_tag' ),
	) );

	register_post_type( 'roundup', array(
		'labels'        => array(
			'name'          => __( 'Roundups', 'e4c-content' ),
			'singular_name' => __( 'Roundup', 'e4c-content' ),
			'add_new_item'  => __( 'Add roundup', 'e4c-content' ),
			'edit_item'     => __( 'Edit roundup', 'e4c-content' ),
			'all_items'     => __( 'All roundups', 'e4c-content' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-list-view',
		'menu_position' => 6,
		'rewrite'       => array( 'slug' => 'best', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
		'show_in_rest'  => true,
		'taxonomies'    => array( 'cat-category', 'post_tag' ),
	) );
}
