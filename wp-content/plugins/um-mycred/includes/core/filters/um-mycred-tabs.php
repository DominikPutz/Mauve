<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adds a main tab to display badges in profile
 *
 * @param array $tabs
 *
 * @return array
 */
function um_mycred_add_tab( $tabs ) {
	if ( ! function_exists( 'mycred_get_users_badges' ) ) {
		return $tabs;
	}

	$tabs['badges'] = array(
		'name' => __( 'Badges', 'um-mycred' ),
		'icon' => 'um-icon-ribbon-b',
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_mycred_add_tab', 2000, 1 );


/**
 * Add tabs based on user
 *
 * @param array $tabs
 *
 * @return array
 */
function um_mycred_user_add_tab( $tabs ) {
	if ( empty( $tabs['badges'] ) ) {
		return $tabs;
	}

	$display_name = um_user( 'display_name' );
	if ( strstr( $display_name, ' ' ) ) {
		$display_name = explode( ' ', $display_name );
		$display_name = $display_name[0];
	}

	$tabs['badges']['subnav_default'] = 'my_badges';
	$tabs['badges']['subnav'] = array(
		'my_badges'     => ( um_is_myprofile() ) ? __( 'Your Badges', 'um-mycred' ) : sprintf( __( '%s\'s Badges', 'um-mycred' ), $display_name ),
		'all_badges'    => __( 'All Badges', 'um-mycred' ),
	);

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_mycred_user_add_tab', 2000, 1 );