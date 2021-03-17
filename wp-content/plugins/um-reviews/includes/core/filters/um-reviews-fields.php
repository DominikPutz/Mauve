<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return mixed
 */
function um_reviews_add_field( $fields ) {

	$fields['user_rating'] = array(
		'title'             => __( 'User Rating', 'um-reviews' ),
		'metakey'           => 'user_rating',
		'type'              => 'text',
		'label'             => __( 'User Rating', 'um-reviews' ),
		'required'          => 0,
		'public'            => 1,
		'editable'          => 0,
		'icon'              => '',
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	return $fields;

}
add_filter( 'um_predefined_fields_hook', 'um_reviews_add_field', 20, 1 );


/**
 * Show rating at frontend
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_reviews_show_rating( $value, $data ) {
	wp_enqueue_script( 'um_reviews' );
	wp_enqueue_style( 'um_reviews' );

	return '<span class="um-reviews-avg" data-number="5" data-score="'. UM()->Reviews()->api()->get_rating( um_user( 'ID' ) ) . '"></span>';
}
add_filter( 'um_profile_field_filter_hook__user_rating', 'um_reviews_show_rating', 99, 2 );