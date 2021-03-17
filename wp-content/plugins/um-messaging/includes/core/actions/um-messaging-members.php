<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete messages on user delete
 *
 * @param $user_id
 */
function um_delete_user_messages( $user_id ) {
	//Update with delete old messages conversations
	global $wpdb;

	$conversation_ids = wp_cache_get( "um_all_conversations:$user_id", 'um_messaging' );
	if ( false === $conversation_ids ) {
		$conversation_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT conversation_id
			FROM {$wpdb->prefix}um_conversations
			WHERE user_a = %d OR
				  user_b = %d",
			$user_id,
			$user_id
		) );
		wp_cache_set( "um_all_conversations:$user_id", $conversation_ids, 'um_messaging' );
	}

	$wpdb->query( $wpdb->prepare(
		"DELETE
		FROM {$wpdb->prefix}um_conversations
		WHERE user_a = %d OR
			  user_b = %d",
		$user_id,
		$user_id
	) );

	wp_cache_delete( "um_all_conversations:$user_id", 'um_messaging' );
	wp_cache_delete( "um_conversations:$user_id", 'um_messaging' );
	wp_cache_delete( "um_conversations:all", 'um_messaging' );

	$wpdb->query( $wpdb->prepare(
		"DELETE
		FROM {$wpdb->prefix}um_messages
		WHERE recipient = %d OR
			  author = %d",
		$user_id,
		$user_id
	) );

	if ( ! empty( $conversation_ids ) ) {
		foreach ( $conversation_ids as $id ) {
			wp_cache_delete( "um_conversation_messages_limit:{$id}", 'um_messaging' );
			wp_cache_delete( "um_new_messages:{$id}", 'um_messaging' );
			wp_cache_delete( "um_conversation_messages:{$id}", 'um_messaging' );
			wp_cache_delete( "um_unread_messages:{$id}:{$user_id}", 'um_messaging' );
		}
	}
	wp_cache_delete( "um_unread_messages:$user_id", 'um_messaging' );
	wp_cache_delete( "um_messages:$user_id", 'um_messaging' );
	wp_cache_delete( "um_messages:all", 'um_messaging' );
}
add_action( 'um_delete_user', 'um_delete_user_messages', 10, 1 );


/**
 * @param $user_id
 */
function remove_error_form_cookie( $user_id ) {
	if ( isset( $_COOKIE['um_messaging_invite_login'] ) ) {
		unset( $_COOKIE['um_messaging_invite_login'] );
		setcookie( "um_messaging_invite_login", null, -1, '/' );
	}
}
add_action( 'um_on_login_before_redirect', 'remove_error_form_cookie' );


/**
 * @param $data
 */
function add_error_form_cookie( $data ) {
	if ( ! empty( $_POST ) ) {
		setcookie( "um_messaging_invite_login", json_encode( $_POST ), time()+3600, '/' );
	}
}
add_action( 'um_user_login_extra_hook', 'add_error_form_cookie' );