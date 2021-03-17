<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Move to Trash Review
 *
 * @param $postid
 */
function trash_um_review( $postid ) {
	if ( ! did_action( 'trash_post' ) ) {
		update_post_meta( $postid, '_status', 0 );
		UM()->Reviews()->api()->adjust_user_rating_by_review( $postid );
	}
}
add_action( 'trash_um_review', 'trash_um_review' );


/**
 * Restore from Trash Review
 *
 * @param $postid
 */
function untrash_um_review( $postid ){
	if ( get_post_type( $postid ) !== 'um_review' ) {
		return;
	}
	update_post_meta( $postid, '_status', 1 );
	UM()->Reviews()->api()->adjust_user_rating_by_review( $postid );
}
add_action( 'untrash_post', 'untrash_um_review' );