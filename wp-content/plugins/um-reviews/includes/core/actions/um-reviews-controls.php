<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Block user from adding review
 *
 * @param $action
 * @param $user_id
 */
function um_reviews_process_user_admin( $action, $user_id ) {
	if ( ! UM()->roles()->um_current_user_can( 'edit', $user_id ) ) return;

	if ( $action == 'um_block_add_review' ) {
		update_user_meta( $user_id, '_cannot_add_review', 1 );
		exit( wp_redirect( UM()->permalinks()->get_current_url( true ) ) );
	}

	if ( $action == 'um_unblock_add_review' ) {
		delete_user_meta( $user_id, '_cannot_add_review' );
		exit( wp_redirect( UM()->permalinks()->get_current_url( true ) ) );
	}
}
add_action( 'um_action_user_request_hook', 'um_reviews_process_user_admin', 10, 2 );


/**
 * Allowed permissions
 *
 * @param $user_id
 * @param $reviewer_id
 * @param $my_id
 * @param $review_id
 */
function um_review_front_actions( $user_id, $reviewer_id, $my_id, $review_id ) {
	$args_t = compact( 'my_id', 'review_id', 'reviewer_id', 'user_id' );
	UM()->get_template( 'review-front-actions.php', um_reviews_plugin, $args_t, true );
}
add_action( 'um_review_front_actions', 'um_review_front_actions', 99, 4 );