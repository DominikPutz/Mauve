<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Notifications tab to account page
 *
 * @param array $tabs
 * @return array
 */
function um_mc_account_notification_tab( $tabs ) {

	if ( empty( $tabs[ 400 ][ 'notifications' ] ) ) {
		$tabs[ 400 ][ 'notifications' ] = array(
			'icon'				 => 'um-faicon-envelope',
			'title'				 => __( 'Notifications', 'um-mailchimp' ),
			'submit_title' => __( 'Update Notifications', 'um-mailchimp' ),
		);
	}

	return $tabs;
}

add_filter( 'um_account_page_default_tabs_hook', 'um_mc_account_notification_tab', 10, 1 );

/**
 * Show mailchimp lists in account
 *
 * @param $output
 * @return string
 */
function um_mc_account_tab( $output ) {

	$user_id = um_user( "ID" );

	$my_lists = UM()->Mailchimp()->api()->get_lists_my( $user_id );
	$wp_lists = UM()->Mailchimp()->api()->get_wp_lists( true );

	$notification_lists = array();

	foreach ( $wp_lists as $wp_list ) {

		$list_id = $wp_list->list_id;

		$mc_member = UM()->Mailchimp()->api()->mc_get_member( $list_id, $user_id );

		$mc_groups = UM()->Mailchimp()->api()->mc_get_interest_categories_array( $list_id );
		$member_groups = isset( $mc_member[ "interests" ] ) ? $mc_member[ "interests" ] : array();

		$mc_tags = UM()->Mailchimp()->api()->mc_get_tags_array( $list_id );
		$member_tags = UM()->Mailchimp()->api()->mc_get_member_tags_array( $list_id, $user_id );

		$nl = array(
			'enabled'		       => empty( $my_lists[ $list_id ] ) ? false : (boolean) $my_lists[ $list_id ],
			'groups'					 => $mc_groups,
			'groups-selected'  => $member_groups,
			'list_id'					 => $list_id,
			'member'					 => $mc_member,
			'name'					   => "um-mailchimp[$list_id]",
			'tags'						 => $mc_tags,
			'tags-selected'    => $member_tags,
			'wp_list'          => $wp_list,
		);

		$notification_lists[ $list_id ] = $nl;
	}

	$t_args = compact( 'list_id', 'my_lists', 'notification_lists', 'user_id' );
	$output .= UM()->get_template( 'account_email_newsletters.php', um_mailchimp_plugin, $t_args );

	return $output;
}

add_filter( 'um_account_content_hook_notifications', 'um_mc_account_tab', 100 );

/**
 * Add custom error message
 *
 * @param $err
 * @param $msg
 * @return string
 */
function um_mc_custom_error_message_handler( $err, $msg ) {
	return esc_html__( $msg, 'um-mailchimp' );
}

add_filter( 'um_custom_error_message_handler', 'um_mc_custom_error_message_handler', 20, 2 );
