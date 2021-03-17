<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add tab for reviews
 *
 * @param array $tabs
 *
 * @return array
 */
function um_reviews_add_tab( $tabs ) {
	$tabs['reviews'] = array(
		'name' => __( 'Reviews', 'um-reviews' ),
		'icon' => 'um-faicon-star'
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_reviews_add_tab', 800 );


/**
 * Add tabs based on user
 *
 * @param array $tabs
 *
 * @return array
 */
function um_reviews_user_add_tab( $tabs ) {
	if ( empty( $tabs['reviews'] ) ) {
		return $tabs;
	}

	if ( ! UM()->Reviews()->api()->get_role_tab_privacy() ) {
		unset( $tabs['reviews'] );
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_reviews_user_add_tab', 1000, 1 );