<?php
/**
 * Taxonomies shared by reviews, roundups and guides.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'e4c_content_register_taxonomies' );
/**
 * One product taxonomy across all three content types, so "litter boxes"
 * collects the review, the roundup and the guide on one archive.
 */
function e4c_content_register_taxonomies(): void {
	register_taxonomy( 'cat-category', array( 'review', 'roundup', 'post' ), array(
		'labels'            => array(
			'name'          => __( 'Categories', 'e4c-content' ),
			'singular_name' => __( 'Category', 'e4c-content' ),
			'add_new_item'  => __( 'Add category', 'e4c-content' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'category-of', 'with_front' => false ),
	) );
}
