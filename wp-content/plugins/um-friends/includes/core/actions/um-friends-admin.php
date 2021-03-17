<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete multiselect fields
 *
 * @param $post_id
 */
function um_friends_multi_choice_keys( $post_id ) {
	delete_post_meta( $post_id, '_um_can_friend_roles' );
}
add_action( 'um_admin_before_saving_role_meta', 'um_friends_multi_choice_keys' );


/**
 * Creates options in Role page
 *
 * @param $roles_metaboxes
 *
 * @return array
 */
function um_friends_add_role_metabox( $roles_metaboxes ) {
	$roles_metaboxes[] = array(
		'id'        => "um-admin-form-friends{" . um_friends_path . "}",
		'title'     => __( 'Friends', 'um-friends' ),
		'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'    => 'um_role_meta',
		'context'   => 'normal',
		'priority'  => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_friends_add_role_metabox', 10, 1 );


/**
 * When user is removed all their data should be removed
 *
 * @param $user_id
 */
function um_friends_delete_user_data( $user_id ) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare(
		"DELETE 
		FROM {$wpdb->prefix}um_friends 
		WHERE user_id1 = %d OR 
			  user_id2 = %d",
		$user_id,
		$user_id
	) );
}
add_action( 'um_delete_user', 'um_friends_delete_user_data', 10, 1 );