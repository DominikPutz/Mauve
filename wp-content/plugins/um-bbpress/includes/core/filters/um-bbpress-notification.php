<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $array
 *
 * @return mixed
 */
function um_bbpress_notifications_types( $array ) {
	$array['bbpress_user_reply'] = array(
		'title'         => __( 'User leaves a reply to bbpress topic', 'um-bbpress' ),
		'template'      => '<strong>{member}</strong> has <strong>replied</strong> to a topic you started on the forum.',
		'account_desc'  => __( 'When a member replies to one of my topics', 'um-bbpress' ),
	);

	$array['bbpress_guest_reply'] = array(
		'title'         => __( 'Guest leaves a reply to bbpress topic', 'um-bbpress' ),
		'template'      => 'A guest has <strong>replied</strong> to a topic you started on the forum.',
		'account_desc'  => __( 'When a guest replies to one of my topics', 'um-bbpress' ),
	);

	return $array;
}
add_filter( 'um_notifications_core_log_types', 'um_bbpress_notifications_types', 9999 , 1 );