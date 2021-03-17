<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend privacy tab options
 *
 * @param string $args
 * @param array $shortcode_args
 *
 * @return string
 */
function um_activity_account_privacy_fields( $args, $shortcode_args ) {
	if ( isset( $shortcode_args['wall_privacy'] ) && 0 == $shortcode_args['wall_privacy'] ) {
		return $args;
	}

	if ( UM()->options()->get( 'activity_enable_privacy' ) ) {
		$args = $args . ',wall_privacy';
	}

	return $args;
}
add_filter( 'um_account_tab_privacy_fields', 'um_activity_account_privacy_fields', 10, 2 );


/**
 * Add field to control wall privacy
 *
 * @param $fields
 *
 * @return array
 */
function um_activity_account_privacy_fields_add( $fields ) {
	$array = array(
		0 => __( 'Public', 'um-activity' ),
		1 => __( 'Members', 'um-activity' ),
		2 => __( 'Only me', 'um-activity' ),
	);

	$wall_privacy = apply_filters( 'um_activity_wall_privacy_dropdown_values', $array );

	$fields['wall_privacy'] = array(
		'title'         => __( 'Who can see your activity wall?', 'um-activity' ),
		'metakey'       => 'wall_privacy',
		'type'          => 'select',
		'label'         => __( 'Who can see your activity wall?', 'um-activity' ),
		'required'      => 0,
		'public'        => 1,
		'editable'      => 1,
		'default'       => 0,
		'options'       => $wall_privacy,
		'options_pair'  => 1,
		'allowclear'    => 0,
		'account_only'  => true,
	);

	UM()->account()->add_displayed_field( 'wall_privacy', 'privacy' );

	return $fields;
}
add_filter( 'um_predefined_fields_hook', 'um_activity_account_privacy_fields_add', 10, 1 );