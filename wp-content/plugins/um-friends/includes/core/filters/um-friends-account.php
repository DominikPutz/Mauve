<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add Notifications tab to account page
 *
 * @param array $tabs
 * @return array
 */
function um_friends_account_notification_tab( $tabs ) {

	if ( empty( $tabs[400]['notifications'] ) ) {
		$tabs[400]['notifications'] = array(
			'icon'          => 'um-faicon-envelope',
			'title'         => __( 'Notifications', 'um-friends' ),
			'submit_title'  => __( 'Update Notifications', 'um-friends' ),
		);
	}

	return $tabs;
}
add_filter( 'um_account_page_default_tabs_hook', 'um_friends_account_notification_tab', 10, 1 );


/**
 * Show friends notifications in account tab
 *
 * @param $output
 * @param $shortcode_args
 * @return string
 */
function um_friends_account_tab( $output, $shortcode_args ) {
	if( isset( $shortcode_args[ '_enable_new_friend' ] ) && !$shortcode_args[ '_enable_new_friend' ] ) {
		return $output;
	}

	$_enable_new_friend = UM()->Friends_API()->api()->enabled_email( get_current_user_id() );

	UM()->account()->add_displayed_field( '_enable_new_friend', 'notifications' );

	$t_args = compact( '_enable_new_friend' );
	$output .= UM()->get_template( 'account-notifications.php', um_friends_plugin, $t_args );

	return $output;
}
add_filter( 'um_account_content_hook_notifications', 'um_friends_account_tab', 50, 2 );


/**
 * @param $array
 *
 * @return mixed
 */
function um_friends_notes_privacy( $array ) {
	$array['friends'] = __( 'Friends only', 'um-friends' );
	return $array;
}
add_filter( 'um_user_bookmarks_privacy_dropdown_values', 'um_friends_notes_privacy', 10, 1 );


/**
 * @param $can_view
 * @param $privacy
 * @param $user_id
 *
 * @return bool
 */
function um_friends_bookmarks_privacy( $can_view, $privacy, $user_id ) {
	if ( $privacy == 'friends' && $user_id ) {
		return UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() );
	}

	return $can_view;
}
add_filter( 'um_user_bookmarks_custom_privacy', 'um_friends_bookmarks_privacy', 10, 3 );