<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Works on inserting/updating wall posts
 *
 * @param $content
 * @param $user_id
 * @param $post_id
 * @param $status
 *
 * @return mixed
 */
function um_activity_mention( $content, $user_id, $post_id, $status ) {
	$content = apply_filters( 'um_activity_mention_integration', $content, $user_id, $post_id, $status );
	return $content;
}
add_filter( 'um_activity_insert_post_content_filter', 'um_activity_mention', 99, 4 );
add_filter( 'um_activity_update_post_content_filter', 'um_activity_mention', 99, 4 );