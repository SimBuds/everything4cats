<?php
/**
 * Field groups, registered in PHP so they are version-controlled and deploy
 * with the code rather than living in the database.
 *
 * Written against ACF's API and provided by Secure Custom Fields, the
 * WordPress.org fork of ACF, which is installed from scripts/plugins.txt. The
 * fork keeps ACF's function names, so nothing here is fork-specific and this
 * file would work unchanged against ACF Pro. See plugins.txt for why the
 * dependency moved off the commercial plugin on 2026-08-13.
 *
 * Field names match what the theme reads through e4c_field(): the theme falls
 * back to raw post meta of the same name when the plugin is inactive, so
 * keeping these names stable is a contract between the two.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/include_fields', 'e4c_content_register_fields' );
/**
 * Registers the review and roundup field groups.
 */
function e4c_content_register_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'                   => 'group_e4c_review',
		'title'                 => __( 'Review', 'e4c-content' ),
		'menu_order'            => 0,
		'position'              => 'acf_after_title',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'active'                => true,
		'show_in_rest'          => true,
		'location'              => array(
			array(
				array( 'param' => 'post_type', 'operator' => '==', 'value' => 'review' ),
			),
		),
		'fields'                => array(
			array(
				'key'         => 'field_e4c_dek',
				'label'       => __( 'Dek', 'e4c-content' ),
				'name'        => 'e4c_dek',
				'type'        => 'textarea',
				'rows'        => 2,
				'maxlength'   => 200,
				'instructions' => __( 'One sentence under the headline. Also used on cards when there is no excerpt.', 'e4c-content' ),
			),
			array(
				'key'         => 'field_e4c_verdict',
				'label'       => __( 'Verdict', 'e4c-content' ),
				'name'        => 'e4c_verdict',
				'type'        => 'textarea',
				'rows'        => 2,
				'maxlength'   => 180,
				'instructions' => __( 'The call, in plain words. Set in the display face at the top of the page.', 'e4c-content' ),
			),
			array(
				'key'         => 'field_e4c_price',
				'label'       => __( 'Price as tested', 'e4c-content' ),
				'name'        => 'e4c_price',
				'type'        => 'text',
				'placeholder' => '$48 CAD',
			),
			array(
				'key'         => 'field_e4c_tested_for',
				'label'       => __( 'In use for', 'e4c-content' ),
				'name'        => 'e4c_tested_for',
				'type'        => 'text',
				'placeholder' => 'nine weeks',
				'instructions' => __( 'How long it lived in the house. Shown in the byline.', 'e4c-content' ),
			),
			array(
				'key'         => 'field_e4c_buy_url',
				'label'       => __( 'Where to buy', 'e4c-content' ),
				'name'        => 'e4c_buy_url',
				'type'        => 'url',
				'instructions' => __( 'Outbound link. The compliance plugin tags it and adds the rel attributes; do not add tracking parameters by hand.', 'e4c-content' ),
			),
			array(
				'key'          => 'field_e4c_pros',
				'label'        => __( 'What works', 'e4c-content' ),
				'name'         => 'e4c_pros',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Add point', 'e4c-content' ),
				'min'          => 0,
				'max'          => 6,
				'sub_fields'   => array(
					array(
						'key'   => 'field_e4c_pro_text',
						'label' => __( 'Point', 'e4c-content' ),
						'name'  => 'text',
						'type'  => 'text',
					),
				),
			),
			array(
				'key'          => 'field_e4c_cons',
				'label'        => __( 'What does not', 'e4c-content' ),
				'name'         => 'e4c_cons',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Add point', 'e4c-content' ),
				'min'          => 0,
				'max'          => 6,
				'sub_fields'   => array(
					array(
						'key'   => 'field_e4c_con_text',
						'label' => __( 'Point', 'e4c-content' ),
						'name'  => 'text',
						'type'  => 'text',
					),
				),
			),
			array(
				'key'          => 'field_e4c_specs',
				'label'        => __( 'Specifications', 'e4c-content' ),
				'name'         => 'e4c_specs',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Add row', 'e4c-content' ),
				'sub_fields'   => array(
					array(
						'key'   => 'field_e4c_spec_label',
						'label' => __( 'Label', 'e4c-content' ),
						'name'  => 'label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_e4c_spec_value',
						'label' => __( 'Value', 'e4c-content' ),
						'name'  => 'value',
						'type'  => 'text',
					),
				),
			),
		),
	) );

	acf_add_local_field_group( array(
		'key'             => 'group_e4c_roundup',
		'title'           => __( 'Roundup', 'e4c-content' ),
		'position'        => 'acf_after_title',
		'label_placement' => 'top',
		'active'          => true,
		'show_in_rest'    => true,
		'location'        => array(
			array(
				array( 'param' => 'post_type', 'operator' => '==', 'value' => 'roundup' ),
			),
		),
		'fields'          => array(
			array(
				'key'       => 'field_e4c_roundup_dek',
				'label'     => __( 'Dek', 'e4c-content' ),
				'name'      => 'e4c_dek',
				'type'      => 'textarea',
				'rows'      => 2,
				'maxlength' => 200,
			),
			array(
				'key'          => 'field_e4c_picks',
				'label'        => __( 'Picks', 'e4c-content' ),
				'name'         => 'e4c_picks',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => __( 'Add pick', 'e4c-content' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_e4c_pick_review',
						'label'         => __( 'Review', 'e4c-content' ),
						'name'          => 'review',
						'type'          => 'post_object',
						'post_type'     => array( 'review' ),
						'return_format' => 'id',
						'instructions'  => __( 'The review this pick points at. Title, image and price come from it, so they cannot drift.', 'e4c-content' ),
					),
					array(
						'key'   => 'field_e4c_pick_award',
						'label' => __( 'Award', 'e4c-content' ),
						'name'  => 'award',
						'type'  => 'text',
						'placeholder' => 'Best for small flats',
					),
					array(
						'key'   => 'field_e4c_pick_why',
						'label' => __( 'Why this one', 'e4c-content' ),
						'name'  => 'why',
						'type'  => 'textarea',
						'rows'  => 2,
					),
				),
			),
		),
	) );
}
