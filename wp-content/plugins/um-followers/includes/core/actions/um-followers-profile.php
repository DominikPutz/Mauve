<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add "follows you" if the user is following current user
 */
function um_followers_add_state() {
	wp_enqueue_style( 'um_followers' );
	wp_enqueue_script( 'um_followers' );

	if ( ! is_user_logged_in() || ! um_profile_id() ) {
		return;
	}

	if ( get_current_user_id() == um_profile_id() ) {
		return;
	}

	if ( UM()->Followers_API()->api()->followed( get_current_user_id(), um_profile_id() ) ) {
		echo '<span class="um-follows-you">'. __( 'follows you', 'um-followers' ) . '</span>';
	}

}
add_action( 'um_after_profile_name_inline', 'um_followers_add_state', 200 );


/**
 * Followers List
 */
function um_profile_content_followers_default() {
	echo do_shortcode('[ultimatemember_followers user_id="' . um_profile_id() . '" /]');
}
add_action( 'um_profile_content_followers_default', 'um_profile_content_followers_default' );


/**
 * Following List
 *
 */
function um_profile_content_following_default() {
	echo do_shortcode('[ultimatemember_following user_id="' . um_profile_id() . '" /]');
}
add_action( 'um_profile_content_following_default', 'um_profile_content_following_default' );


/**
 * Customize the nav bar
 */
function um_followers_add_profile_bar() {
	echo do_shortcode('[ultimatemember_followers_bar user_id="' . um_profile_id() . '" /]');
}
add_action( 'um_profile_navbar', 'um_followers_add_profile_bar', 4 );


/**
 * User suggestions for Social Activity
 *
 * @param $data
 * @param $term
 *
 * @return array
 */
function um_followers_ajax_get_user_suggestions( $data, $term ) {
	if ( ! UM()->options()->get( 'activity_followers_mention' ) ) {
		return $data;
	}

	$term = str_replace( '@', '', $term );
	if ( empty( $term ) ) {
		return $data;
	}

	$users_data = array();

	$user_id = get_current_user_id();

	$following = UM()->Followers_API()->api()->following( $user_id );
	if ( $following ) {
		foreach ( $following as $k => $arr ) {
			/**
			 * @var int $user_id1
			 */
			extract( $arr );
			um_fetch_user( $user_id1 );

			if ( ! stristr( um_user( 'display_name' ), $term ) ) {
				continue;
			}
			$users_data[ $user_id1 ]['user_id'] = $user_id1;
			$users_data[ $user_id1 ]['photo'] = get_avatar( $user_id1, 80 );
			$users_data[ $user_id1 ]['name'] = str_replace( $term, '<strong>' . $term . '</strong>', um_user( 'display_name' ) );
			$users_data[ $user_id1 ]['username'] = um_user( 'display_name' );
		}
	}

	$followers = UM()->Followers_API()->api()->followers( $user_id );
	if ( $followers ) {
		foreach ( $followers as $k => $arr ) {
			/**
			 * @var int $user_id2
			 */
			extract( $arr );
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
add_filter( 'um_activity_ajax_get_user_suggestions', 'um_followers_ajax_get_user_suggestions', 10, 2 );