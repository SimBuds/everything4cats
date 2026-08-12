<?php
/**
 * Pattern category. The patterns themselves are auto-registered from
 * patterns/ by core.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'e4c_register_pattern_category' );
/**
 * Groups the theme's patterns under one heading in the inserter.
 */
function e4c_register_pattern_category(): void {
	register_block_pattern_category( 'e4c', array(
		'label' => __( 'Everything4Cats', 'e4c' ),
	) );
}
