<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter user permissions in bbPress
 *
 * @param $meta
 * @param $user_id
 *
 * @return mixed
 */
function um_followers_user_permissions_filter( $meta, $user_id ) {
	if ( ! isset( $meta['can_follow'] ) ) {
		$meta['can_follow'] = 1;
	}

	return $meta;
}
add_filter( 'um_user_permissions_filter', 'um_followers_user_permissions_filter', 10, 4 );



/**
 * Filter for Groups invites
 *
 * @param array $options
 *
 * @return array
 */
function um_followers_groups_invite_people( $options ) {
	$array_invite_people_opts['followers'] = __( 'Followers only', 'um-followers' );
	return $options;
}
add_filter( 'um_groups_invite_people', 'um_followers_groups_invite_people', 10, 1 );