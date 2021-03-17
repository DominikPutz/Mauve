<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add button in cover
 *
 * @param $args
 */
function um_friends_add_button( $args ) {
	if ( $args['cover_enabled'] == 1 ) {
		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$user_id = um_profile_id();
		echo '<div class="um-friends-coverbtn">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	}
}
add_action( 'um_before_profile_main_meta', 'um_friends_add_button' );


/**
 * Add button in case that cover is disabled
 *
 * @param $args
 */
function um_friends_add_button_nocover( $args ) {
	wp_enqueue_script( 'um_friends' );
	wp_enqueue_style( 'um_friends' );

	$user_id = um_profile_id();
	if ( $args['cover_enabled'] != 1 ) {
		echo '<div class="um-friends-nocoverbtn" style="display: block">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	} else {
		echo '<div class="um-friends-nocoverbtn" style="display: none">' . UM()->Friends_API()->api()->friend_button( $user_id, get_current_user_id() ) . '</div>';
	}
}
add_action( 'um_after_profile_header_name_args', 'um_friends_add_button_nocover', 90, 1 );


/**
 * Add friendship state
 *
 * @param $args
 */
function um_friends_add_state( $args ) {
	wp_enqueue_script( 'um_friends' );
	wp_enqueue_style( 'um_friends' );

	if ( ! is_user_logged_in() || ! um_profile_id() ) {
		return;
	}

	if ( get_current_user_id() == um_profile_id() ) {
		return;
	}

	if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), um_profile_id() ) ) {
		echo '<span class="um-friend-you"></span>';
	}

}
add_action( 'um_after_profile_name_inline', 'um_friends_add_state', 200 );


/**
 * Friends List
 *
 * @param $args
 */
function um_profile_content_friends_default( $args ) {
	echo do_shortcode('[ultimatemember_friends user_id="' . um_profile_id() . '"]');
}
add_action( 'um_profile_content_friends_default', 'um_profile_content_friends_default' );
add_action( 'um_profile_content_friends_myfriends', 'um_profile_content_friends_default' );


/**
 * Friend requests List
 *
 * @param $args
 */
function um_profile_content_friends_friendreqs( $args ) {
	echo do_shortcode('[ultimatemember_friend_reqs user_id="' . um_profile_id() . '"]');
}
add_action( 'um_profile_content_friends_friendreqs', 'um_profile_content_friends_friendreqs' );


/**
 * Friend requests sent List
 *
 * @param $args
 */
function um_profile_content_friends_sentreqs( $args ) {
	echo do_shortcode('[ultimatemember_friend_reqs_sent user_id="' . um_profile_id() . '"]');
}
add_action( 'um_profile_content_friends_sentreqs', 'um_profile_content_friends_sentreqs' );


/**
 * User suggestions for Social Activity
 *
 * @param $data
 * @param string $term
 *
 * @return mixed
 */
function um_friends_ajax_get_user_suggestions( $data, $term ) {
	if ( ! UM()->options()->get( 'activity_friends_mention' ) ) {
		return $data;
	}

	$term = str_replace( '@', '', $term );
	if ( empty( $term ) ) {
		return $data;
	}

	$users_data = array();

	$user_id = get_current_user_id();

	$friends = UM()->Friends_API()->api()->friends( $user_id );
	if ( $friends ) {
		foreach ( $friends as $k => $arr ) {
			/**
			 * @var int $user_id1
			 */
			extract( $arr );

			if ( $user_id1 == $user_id ) {
				continue;
			}

			um_fetch_user( $user_id1 );
			if ( ! stristr( um_user( 'display_name' ), $term ) ) {
				continue;
			}

			$users_data[ $user_id1 ]['user_id'] = $user_id1;
			$users_data[ $user_id1 ]['photo'] = get_avatar( $user_id1, 80 );
			$users_data[ $user_id1 ]['name'] = str_replace( $term, '<strong>' . $term . '</strong>', um_user( 'display_name' ) );
			$users_data[ $user_id1 ]['username'] = um_user( 'display_name' );
		}

		foreach ( $friends as $k => $arr ) {
			/**
			 * @var int $user_id2
			 */
			extract( $arr );

			if ( $user_id2 == $user_id ) {
				continue;
			}

			um_fetch_user( $user_id2 );
			if ( ! stristr( um_user( 'display_name' ), $term ) ) {
				continue;
			}

			$users_data[ $user_id2 ]['user_id'] = $user_id2;
			$users_data[ $user_id2 ]['photo'] = get_avatar( $user_id2, 80 );
			$users_data[ $user_id2 ]['name'] = str_replace( $term, '<strong>' . $term . '</strong>', um_user( 'display_name' ) );
			$users_data[ $user_id2 ]['username'] = um_user( 'display_name' );
		}
	}

	if ( ! empty( $users_data ) ) {
		$data = array_merge( $data, $users_data );
	}

	return $data;
}
add_filter( 'um_activity_ajax_get_user_suggestions', 'um_friends_ajax_get_user_suggestions', 10, 2 );