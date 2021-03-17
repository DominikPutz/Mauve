<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Allow hashtags in comments
 *
 * @param $content
 * @param $post_id
 *
 * @return mixed
 */
function um_activity_comment_content_new( $content, $post_id ) {
	UM()->Activity_API()->api()->hashtagit( $post_id, $content, true );
	return $content;
}
add_filter( 'um_activity_comment_content_new', 'um_activity_comment_content_new', 10, 2 );


/**
 * Filter the comment title
 *
 * @param  string $title
 * @param  int $post_id
 *
 * @return string
 * @uses  the_title filter hook
 */
function um_activity_recent_comments( $title, $post_id = null ) {

	if ( is_numeric( $title ) && $post_id ) {
		$post = get_post( $post_id );
		if ( $post && $post->post_type == 'um_activity' ) {
			$activity_id = UM()->options()->get( UM()->options()->get_core_page_id( 'activity' ) );
			$activity_title = get_the_title( $activity_id );
			$title = sprintf( __( '#%1d %2s post', 'um-activity' ), $post_id, lcfirst( $activity_title ) );
		}
	}

	return $title;
}
add_filter( 'the_title', 'um_activity_recent_comments', 10, 2 );


/**
 * Filter comment author link
 * @param  string $link
 * @param  string $comment
 * @param  array $args
 * @param  array $cpage
 *
 * @uses  get_comment_link filter hook
 *
 * @return string
 */
function um_activity_get_comment_author_link( $link, $comment, $args, $cpage ) {
	if ( strpos( $link, "/um_activity/" ) > -1 ) {
		$arr_link = explode("/", $link );
		$post_id = isset( $arr_link[4] )? $arr_link[4]: 0;
		$url = UM()->Activity_API()->api()->get_permalink( $post_id );
		$link = esc_url( $url );
	}
	return $link;
}
add_filter( 'get_comment_link', 'um_activity_get_comment_author_link', 999, 4 );


/**
 * Exclude social activity comments
 * @param array $args
 *
 * @return array
 */
function um_activity_recent_comments_args( $args ) {
	$args['type__not_in'] = array( 'um-social-activity' );
	return $args;
}
add_filter( 'widget_comments_args', 'um_activity_recent_comments_args' );