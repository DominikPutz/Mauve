<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Display badges in header
 */
function um_mycred_show_badges_header() {
	if ( ! UM()->options()->get( 'mycred_show_badges_in_header' ) ) {
		return;
	}
	echo UM()->myCRED()->show_badges( um_profile_id() );
}
add_action( 'um_after_profile_header_name', 'um_mycred_show_badges_header' );


/**
 * Default badges tab
 *
 * @param $args
 */
function um_profile_content_badges_default( $args ) {
	echo UM()->myCRED()->show_badges( um_profile_id() );
}
add_action( 'um_profile_content_badges_default', 'um_profile_content_badges_default' );


/**
 * Show user badges
 *
 * @param $args
 */
function um_profile_content_badges_my_badges( $args ) {
	echo UM()->myCRED()->show_badges( um_profile_id() );
}
add_action( 'um_profile_content_badges_my_badges', 'um_profile_content_badges_my_badges' );


/**
 * Show all badges
 *
 * @param $args
 */
function um_profile_content_badges_all_badges( $args ) {
	echo UM()->myCRED()->show_badges_all( 2 );
}
add_action( 'um_profile_content_badges_all_badges', 'um_profile_content_badges_all_badges' );