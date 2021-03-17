<?php
if( !defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Send a mail notification
 *
 * @param $user_id1
 * @param $user_id2
 *
 * @return void
 */
function um_user_photos_album_notification( $album_id ) {
	global $current_user;

	$profile_url = um_user_profile_url( $current_user->ID ) . '?profiletab=photos&subnav=albums';
	$site_name = get_bloginfo( 'name' );
	$to = get_bloginfo( 'admin_email' );
	
	switch( sanitize_key($_REQUEST['action']) ) {
		case 'create_um_user_photos_album':
			$album = get_post( $album_id );
			$album_title = $album->post_title;
			$album_action = 'created';
			break;
		case 'delete_um_user_photos_album':
			$album_title = "ID:$album_id";
			$album_action = 'deleted';
			break;
		case 'update_um_user_photos_album':
			$album = get_post( $album_id );
			$album_title = $album->post_title;
			$album_action = 'updated';
			break;
		default:
			$album_action = 'updated';
			$album = get_post( $album_id );
			$album_title = $album->post_title;
			break;
	}

	UM()->mail()->send( $to, 'new_album', array(
			'plain_text'	 => 1,
			'path'				 => um_user_photos_path . 'templates/email/',
			'tags'				 => array(
					'{album_title}',
					'{album_action}',
					'{user_name}',
					'{profile_url}',
					'{site_name}'
			),
			'tags_replace' => array(
					$album_title,
					$album_action,
					$current_user->display_name,
					$profile_url,
					$site_name
			)
	) );
}
add_action( 'um_user_photos_after_album_created', 'um_user_photos_album_notification', 20 );
add_action( 'um_user_photos_after_album_deleted', 'um_user_photos_album_notification', 20 );
add_action( 'um_user_photos_after_album_updated', 'um_user_photos_album_notification', 20 );


/**
 * Extends email notifications settings
 *
 * @param $email_notifications
 *
 * @return mixed
 */
function um_user_photos_mail_notification_album( $email_notifications ) {
	$email_notifications[ 'new_album' ] = array(
			'key'						 => 'new_album',
			'title'					 => __( 'User Photos - Album Email', 'um-user-photos' ),
			'subject'				 => '[{site_name}] User Photo - Album {album_action}',
			'body'					 => 'User "{user_name}" {album_action} album "{album_title}".<br />Click on the following link to see his/her albums:<br />{profile_url}',
			'description'		 => __( 'Send a notification to admin when user create, delete ot update an album', 'um-user-photos' ),
			'recipient'			 => 'admin',
			'default_active' => true
	);

	return $email_notifications;
}
add_filter( 'um_email_notifications', 'um_user_photos_mail_notification_album', 20, 1 );


/**
 * @param $slugs
 *
 * @return mixed
 */
function um_user_photos_email_templates_path_by_slug( $slugs ) {
	$slugs[ 'new_album' ] = um_user_photos_path . 'templates/email/';
	return $slugs;
}
add_filter( 'um_email_templates_path_by_slug', 'um_user_photos_email_templates_path_by_slug', 10, 1 );