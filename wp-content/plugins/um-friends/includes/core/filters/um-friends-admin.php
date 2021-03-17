<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Filter user permissions in bbPress
 *
 * @param $meta
 * @param $user_id
 *
 * @return mixed
 */
function um_friends_user_permissions_filter( $meta, $user_id ) {
	if ( ! isset( $meta['can_friend'] ) ) {
		$meta['can_friend'] = 1;
	}

	return $meta;
}
add_filter( 'um_user_permissions_filter', 'um_friends_user_permissions_filter', 10, 4 );


/**
 * Filter for Groups invites
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_groups_invite_people( $options ) {
	$options['friends'] = __( 'Friends only', 'um-friends' );

	if ( ! empty( $options['followers'] ) ) {
		$options['friends_followers'] = __( 'Friends & Followers only', 'um-friends' );
	}

	return $options;
}
add_filter( 'um_groups_invite_people', 'um_friends_groups_invite_people', 20, 1 );