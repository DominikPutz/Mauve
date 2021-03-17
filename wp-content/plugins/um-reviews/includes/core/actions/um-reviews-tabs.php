<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Default reviews tab
 *
 * @param $args
 */
function um_profile_content_reviews_default( $args ) {

	wp_enqueue_script( 'um_reviews' );
	wp_enqueue_style( 'um_reviews' );

	UM()->get_template( 'review-overview.php', um_reviews_plugin, $args, true );
	UM()->get_template( 'review-add.php', um_reviews_plugin, $args, true );
	UM()->get_template( 'review-edit.php', um_reviews_plugin, $args, true );

	UM()->Reviews()->api()->set_filter();

	$reviews = UM()->Reviews()->api()->get_reviews( um_profile_id() );
	if ( $reviews && is_array( $reviews ) ) {

		$args['reviews'] = $reviews;
		UM()->get_template( 'review-list.php', um_reviews_plugin, $args, true );

	} elseif ( $reviews === -1 ) {

		UM()->get_template( 'review-my.php', um_reviews_plugin, $args, true );

	} else {

		UM()->get_template( 'review-none.php', um_reviews_plugin, $args, true );

	}
}
add_action( 'um_profile_content_reviews_default', 'um_profile_content_reviews_default' );


/**
 * Reviews reply contents
 *
 * @param int $review_id
 * @param int $reviewer_id
 * @param int $profile_id
 */
function um_review_reply_content( $review_id, $reviewer_id, $profile_id ) {

	um_fetch_user( $profile_id );

	if ( is_user_logged_in() && get_current_user_id() == $profile_id && UM()->options()->get( 'enable_review_reply' ) ) {

		$args = compact( 'review_id', 'reviewer_id', 'profile_id' );
		UM()->get_template( 'reply-add.php', um_reviews_plugin, $args, true );

	}

	$replies = UM()->Reviews()->api()->get_replies( $review_id , $profile_id );

	if ( ! empty( $replies ) ) {

		foreach ( $replies as $r ) {

			$args = compact( 'review_id', 'reviewer_id', 'profile_id', 'r' );
			UM()->get_template( 'reply-list.php', um_reviews_plugin, $args, true );

		}
	}

}
add_action( 'um_review_after_review_content', 'um_review_reply_content', 10, 3 );
