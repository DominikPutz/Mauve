<?php
namespace um_ext\um_reviews\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Reviews_Main_API
 * @package um_ext\um_reviews\core
 */
class Reviews_Main_API {


	/**
	 * Reviews_Main_API constructor.
	 */
	function __construct() {

	}


	/**
	 * Recalculate rating
	 * @deprecated since version 2.2
	 * 
	 * @param $postid
	 * @param $old_rating
	 * @param $new_rating
	 */
	function adjust_rating( $postid, $old_rating = null, $new_rating = null ) {
		_deprecated_function( __FUNCTION__, '2.2', 'UM()->Reviews()->api()->adjust_user_rating_by_review( $postid )' );

		$user_id = get_post_meta( $postid, '_user_id', true );

		if ( $new_rating ) {
			update_post_meta( $postid, '_rating', $new_rating );
		}

		$this->adjust_user_rating( $user_id );
	}


	/**
	 * Recalculate rating and update user meta fields
	 * @since 2019-08-06
	 *
	 * @param int $user_id
	 * @return array
	 */
	function adjust_user_rating( $user_id ) {

		$review_posts = get_posts( array(
			'meta_query'    => array(
				'relation'  => 'AND',
				array(
					'key'   => '_status',
					'value' => 1,
					'type'  => 'numeric'
				),
				array(
					'key'   => '_user_id',
					'value' => $user_id,
					'type'  => 'numeric'
				)
			),
			'numberposts'   => -1,
			'post_status'   => 'publish',
			'post_type'     => 'um_review',
		) );

		//update reviews
		$reviews = array_reduce( $review_posts, function ( $carry, $review ) {
			if ( is_object( $review ) ) {
				if( apply_filters( 'um_reviews_allow_multiple_reviews', false ) ){
					$carry[] = $review->_rating;
				} else {
 					$carry[ $review->_reviewer_id ] = $review->_rating;
 				}
			}
			return $carry;
		}, array() );
		update_user_meta( $user_id, '_reviews', $reviews );

		//update reviews compound
		$reviews_compound = array_reduce( $review_posts, function ( $carry, $review ) {
			return ( int ) $carry + $review->_rating;
		}, 0 );
		update_user_meta( $user_id, '_reviews_compound', $reviews_compound );

		//update total reviews
		$reviews_total = count( $review_posts );
		update_user_meta( $user_id, '_reviews_total', $reviews_total );

		//update rating average
		$reviews_avg = 0;
		if ( $reviews_compound > 0 && $reviews_total > 0 ) {
			$reviews_avg = $reviews_compound / $reviews_total;
		}
		update_user_meta( $user_id, '_reviews_avg', ( float ) number_format( $reviews_avg, 2 ) );

		return compact( 'reviews', 'reviews_compound', 'reviews_total', 'reviews_avg' );
	}


	/**
	 * Recalculate rating and update user meta fields if Review was created or removed
	 * @since version 2.2 2019-10-04
	 *
	 * @param int $post_id
	 * @return array
	 */
	function adjust_user_rating_by_review( $post_id ) {
		$user_id = (int) get_post_meta( $post_id, '_user_id', true );

		$user_exists = get_userdata( $user_id );
		if ( ! $user_exists ) {
			return;
		}

		// update users who reviewed the user
		return $this->adjust_user_rating( $user_id );
	}


	/**
	 * Review was created, renew calculations
	 * @param $postid
	 */
	function publish_review( $postid ) {
		update_post_meta( $postid, '_status', 1 );
		$this->adjust_user_rating_by_review( $postid );
	}


	/**
	 * Review was removed, renew calculations
	 * @param $postid
	 */
	function undo_review( $postid ) {
		update_post_meta( $postid, '_status', 0 );
		$this->adjust_user_rating_by_review( $postid );
	}


	/**
	 * @return int
	 */
	function get_filter() {
		if ( isset( $_REQUEST['filter'] ) && absint( $_REQUEST['filter'] ) <= 5 && absint( $_REQUEST['filter'] ) > 0 ) {
			return absint( $_REQUEST['filter'] );
		}
		return 0;
	}


	/**
	 *
	 */
	function set_filter() {
		if ( isset( $_REQUEST['filter'] ) && absint( $_REQUEST['filter'] ) <= 5 && absint( $_REQUEST['filter'] ) > 0 ) {
			$this->rating_filter = absint( $_REQUEST['filter'] );
		} else {
			$this->rating_filter = '';
		}
	}


	/**
	 * Get reviews
	 *
	 * @param $user_id
	 *
	 * @return array|bool|int
	 */
	function get_reviews( $user_id ) {
		$my_review_ = false;

		$args = array(
			'posts_per_page'    => -1,
			'post_type'         => 'um_review',
			'post_status'       => array( 'publish' ),
			'meta_query'        => array(
				'relation'  => 'AND',
				'_user_id'  => array(
					'key'       => '_user_id',
					'value'     => $user_id,
					'compare'   => '='
				),
				'_status'   => array(
					'key'       => '_status',
					'value'     => 1,
					'compare'   => '='
				)
			),
		);

		if ( $this->already_reviewed( $user_id ) ) {
			$args['meta_query'][] = array(
				'key'       => '_reviewer_id',
				'value'     => get_current_user_id(),
				'compare'   => '!='
			);
		}

		if ( $this->rating_filter ) {
			$args['meta_query'][] = array(
				'key'       => '_rating',
				'value'     => $this->rating_filter,
				'compare'   => '='
			);
		}

		$review_query = new \WP_Query( $args );

		if ( isset( $review_query->posts ) && $review_query->found_posts > 0 ) {

			return $review_query->posts;

		} elseif ( $my_review_ == true ) {
			return -1;
		} else {
			return false;
		}
	}


	/**
	 * Get replies
	 *
	 * @param $review_id
	 * @param $profile_id
	 *
	 * @return array|int
	 */
	function get_replies( $review_id, $profile_id  ) {
		if ( ! UM()->options()->get( 'enable_review_reply' ) ) {
			return array();
		}
		
		$comments = get_comments( array(
			'type'      => 'um-reviews',
			'user_id'   => $profile_id,
			'order'     => 'ASC',
			'post_id'   => $review_id
		) );

		return $comments;
	}


	/**
	 * Get review details
	 *
	 * @param $user_id
	 * @param $reviewer_id
	 *
	 * @return int
	 */
	function get_review_detail( $user_id, $reviewer_id ) {

		$args = array(
			'post_type'         => 'um_review',
			'posts_per_page'    => 1,
			'author'            => $user_id,
			'post_status'       => array( 'publish' ),
		);

		$args['meta_query'][] = array(
			'key'       => '_reviewer_id',
			'value'     => $reviewer_id,
			'compare'   => '=',
		);

		$review_query = new \WP_Query( $args );
		$review = $review_query->posts;
		if ( isset( $review[0] ) ) {
			return $review[0];
		}

		return 0;
	}


	/**
	 * Check review pending status
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	function is_pending( $post_id ) {
		$status = get_post_meta( $post_id, '_status', true );
		if ( $status == 0 ) {
			return true;
		}

		return false;
	}


	/**
	 * Check that already reviewed
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function already_reviewed( $user_id ) {

		$allow_multiple = apply_filters( 'um_reviews_allow_multiple_reviews', false );

		if ( $allow_multiple ) {
			return false;
		}

		$args = array(
			'post_type'         => 'um_review',
			'posts_per_page'    => 1,
			'author'            => $user_id,
			'post_status'       => array('publish'),
		);

		$args['meta_query'][] = array(
			'key'       => '_reviewer_id',
			'value'     => get_current_user_id(),
			'compare'   => '='
		);

		$review_query = new \WP_Query( $args );
		$review = $review_query->found_posts;

		if ( $review > 0 ) {
			return true;
		}
		return false;
	}


	/**
	 * Can user edit their review
	 *
	 * @param int $reviewer_id
	 *
	 * @return boolean
	 */
	function can_edit( $reviewer_id ) {
		$can = false;

		if ( $reviewer_id == get_current_user_id() && UM()->roles()->um_user_can( 'can_remove_own_review' ) ) {
			$can = true;
		}

		if ( UM()->roles()->um_user_can( 'can_remove_review' ) ) {
			$can = true;
		}

		return apply_filters( 'um_reviews_can_edit', $can, $reviewer_id );
	}


	/**
	 * @param $review_id
	 *
	 * @return bool
	 */
	function is_flagged( $review_id ) {
		if ( get_post_meta( $review_id, '_flagged', true ) ) {
			return true;
		}
		return false;
	}


	/**
	 * @param $review_id
	 *
	 * @return bool
	 */
	function can_flag( $review_id ) {

		// already flagged
		if ( get_post_meta( $review_id, '_flagged', true ) ) {
			return false;
		}

		// logged in but trying as guest
		if ( UM()->options()->get( 'can_flag_review' ) == 'loggedin' && ! is_user_logged_in() ) {
			return false;
		}

		// reviewed only but not true
		if ( UM()->options()->get( 'can_flag_review' ) == 'reviewed' ) {
			$user_id = get_post_meta( $review_id, '_user_id', true );
			if ( $user_id != get_current_user_id() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check can reply capability
	 *
	 * @param int $profile_id
	 * @param int|null $review_id
	 *
	 * @return bool
	 */
	function can_reply( $profile_id, $review_id = null ) {
		if ( $profile_id == get_current_user_id() && UM()->options()->get( 'enable_review_reply' ) ) {
			return true;
		}
		
		return false;
	}


	/**
	 * @param int $reviewer_id
	 *
	 * @return bool
	 */
	function can_remove( $reviewer_id ) {
		$can = false;

		if ( ( $reviewer_id == get_current_user_id() && UM()->roles()->um_user_can( 'can_remove_own_review' ) ) ) {
			$can = true;
		}

		if ( UM()->roles()->um_user_can( 'can_remove_review' ) ) {
			$can = true;
		}

		return apply_filters( 'um_reviews_can_remove', $can, $reviewer_id );
	}


	/**
	 * @param bool $user_id
	 *
	 * @return bool
	 */
	function is_blocked( $user_id = false ) {
		$user_id = ( $user_id ) ? $user_id : get_current_user_id();

		if ( get_user_meta( $user_id, '_cannot_add_review', true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Can leave a review?
	 *
	 * @param bool $user_id
	 * @return bool
	 */
	function can_review( $user_id = false ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( $this->is_blocked() ) {
			return false;
		}

		if ( ! UM()->roles()->um_user_can( 'can_review' ) ) {
			return false;
		}

		$roles = UM()->roles()->get_all_user_roles( $user_id );
		if ( UM()->roles()->um_user_can( 'can_review_roles' ) &&
		     ( empty( $roles ) || count( array_intersect( $roles, UM()->roles()->um_user_can( 'can_review_roles' ) ) ) <= 0 ) ) {
			return false;
		}

		if ( $this->already_reviewed( $user_id ) ) {
			return false;
		}

		if ( $user_id && $user_id == get_current_user_id() ) {
			return false;
		}

		return true;
	}


	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	function avg_rating( $user_id = null ) {

		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$total_ratings = (int) get_user_meta( $user_id, '_reviews_total', true );

		if ( $total_ratings != 0 ) {
			$result = sprintf( _n( '%s average based on %s review.', '%s average based on %s reviews.', $total_ratings, 'um-reviews' ), number_format( $this->get_avg_rating( $user_id ), 2 ), number_format( $total_ratings ) );
		} else {
			if ( um_is_myprofile() ) {
				$result = __( 'Nobody has reviewed you yet.', 'um-reviews' );
			} else {
				$result = __( 'Nobody has reviewed this user yet.', 'um-reviews' );
			}
		}

		return $result;
	}


	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	function get_rating( $user_id = null ) {
		return $this->get_avg_rating( $user_id );
	}


	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	function get_avg_rating( $user_id = null ) {
		$user_id = ( $user_id ) ? $user_id : um_profile_id();

		$reviews_compound = get_user_meta( $user_id, '_reviews_compound', true );
		$reviews_total = get_user_meta( $user_id, '_reviews_total', true );

		$result = 0;
		if( $reviews_compound > 0 && $reviews_total > 0 ) {
			$result = $reviews_compound / $reviews_total;
		}

		return number_format( $result, 2 );
	}


	/**
	 * @param null $user_id
	 *
	 * @return int
	 */
	function get_reviews_count( $user_id = null ) {
		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$result = (int) get_user_meta( $user_id, '_reviews_total', true );
		return $result;
	}


	/**
	 * @param $i
	 *
	 * @return string
	 */
	function generate_star_rating_url( $i ) {
		$nav_link = UM()->permalinks()->get_current_url( get_option('permalink_structure') );
		$nav_link = remove_query_arg( 'um_action', $nav_link );
		$nav_link = remove_query_arg( 'subnav', $nav_link );
		$nav_link = add_query_arg('profiletab', 'reviews', $nav_link );
		$nav_link = add_query_arg('filter', $i, $nav_link );
		return $nav_link;
	}


	/**
	 * Render review-detail template
	 */
	function get_details() {

		$vals = array();
		$reviews = get_user_meta( um_profile_id(), '_reviews', true );
		$reviews_total = get_user_meta( um_profile_id(), '_reviews_total', true );

		if( empty( $reviews ) || !is_array( $reviews ) ) {
			return;
		}

		if( apply_filters( 'um_reviews_allow_multiple_reviews', false ) ) {

			$vals = array_count_values( $reviews );
			$reviews_total = count( $reviews );

		} else {

			$arr_reviews = array();
			foreach( $reviews as $reviewer_id => $rating ) {
				if( empty( $rating ) ) {
					$arr_reviews[ $reviewer_id ] = 0;
				}
				else {
					$arr_reviews[ $reviewer_id ] = $rating;
				}
			}

			$vals = array_count_values( $arr_reviews );
			$reviews_total = count( $arr_reviews );
		}

		for ( $i = 5; $i >= 1; $i-- ) {

			$count_of_reviews = ( isset( $vals[ $i ] ) && $reviews_total > 0 ) ? $vals[ $i ] : 0;
			$progress = $reviews_total ? number_format( $count_of_reviews / $reviews_total * 100 ) : 0;
			$star_rating_url = $this->generate_star_rating_url( $i );
			$star_rating_text = ( $this->get_filter() == $i ) ? '<strong>' . sprintf( __( '%s Star', 'um-reviews' ), $i ) . '</strong>' : sprintf( __( '%s Star', 'um-reviews' ), $i );

			$args_t = compact('count_of_reviews', 'progress', 'star_rating_text', 'star_rating_url');
			UM()->get_template( 'review-detail.php', um_reviews_plugin, $args_t, true );

		}
	}


	/**
	 * @param int $reviewer_id
	 *
	 * @return bool
	 */
	function is_own_review( $reviewer_id ) {
		if ( $reviewer_id == get_current_user_id() ) {
			return true;
		}

		return false;
	}


	/**
	 *  Checks the user role tab privacy
	 */
	function get_role_tab_privacy() {
		if ( um_is_user_himself() ) {
			um_reset_user();
		} else {
			um_fetch_user( um_get_requested_user() );
		}

		$can_review_tabs = um_user( 'can_have_reviews_tab' );
		if ( empty( $can_review_tabs ) ) {
			return false;
		}

		return true; // default
	}


	/**
	 * Remove reviews from deleted old users
	 */
	function flush_reviews() {

		$user_ids = get_users( array(
			'fields' => 'ids'
		) );

		$args = array(
			'post_type'         => 'um_review',
			'posts_per_page'    => -1,
			'post_status'       => array( 'publish' ),
			'meta_query'        => array(
				'relation'  => 'OR',
				array(
					'key'     => '_reviewer_id',
					'value'   => $user_ids,
					'compare' => 'NOT IN'
				),
				array(
					'key'     => '_user_id',
					'value'   => $user_ids,
					'compare' => 'NOT IN'
				)
			),
			'fields' => 'ids'
		);

		$reviews = get_posts( $args );

		if ( empty( $reviews ) ) {
			return;
		}

		foreach ( $reviews as $review_id ) {
			$user_id = get_post_meta( $review_id, '_user_id', true );
			$this->undo_review( $review_id );
			wp_delete_post( $review_id, true );
			$this->adjust_user_rating( $user_id );
		}
	}


	/**
	 * add a review to user
	 */
	function ajax_review_add() {
		UM()->check_ajax_nonce();

		/**
		 * @var string $action ( review_add || review_edit )
		 * @var int $reviewer_id
		 * @var int $user_id
		 * @var $rating
		 * @var string $title
		 * @var string $content
		 * @var int $review_id
		 * @var bool $reviewer_publish
		 */
		extract( $_POST );

		if ( ! isset( $action ) || ! isset( $reviewer_id ) || ! isset( $user_id ) ||
		     ! isset( $rating ) || ! isset( $title ) || ! isset( $content ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-reviews' ) );
		}

		//check capability to review user
		if ( $action != 'um_review_edit' && ! $this->can_review( $user_id ) ) {
			wp_send_json_error( __( 'You can not rate this user.', 'um-reviews' ) );
		}

		if ( empty( $rating ) ) {
			wp_send_json_error( __( 'Please add a rating.', 'um-reviews' ) );
		}

		if ( empty( $title ) ) {
			wp_send_json_error( __( 'You must provide a title.', 'um-reviews' ) );
		}

		if ( empty( $content ) ) {
			wp_send_json_error( __( 'You must provide review content.', 'um-reviews' ) );
		}


		$can_publish_review = ! empty( $reviewer_publish );

		//prepare review array
		$output = array(
			'rating'    => $rating,
			'title'     => stripslashes( $title ),
			'content'   => wpautop( stripslashes( $content ) ),
		);

		switch( $action ) {
			case 'um_review_edit':

				//validate review
				$review = get_post( $review_id );
				if( empty( $review ) || $review->post_status !== 'publish' ) {
					$output[ 'pending' ] = __( 'Can not edit. This review was removed.', 'um-reviews' );
					break;
				}

				//update review
				wp_update_post( array(
					'ID'            => $review_id,
					'post_title'    => $output['title'],
					'post_content'  => $output['content']
				) );
				
				update_post_meta( $review_id, '_rating', $output['rating'] );

				if ( $can_publish_review ) {
					$this->publish_review( $review_id );
				} else {
					$output['pending'] = __( 'This review will be moderated by an admin before it is live.', 'um-reviews' );
				}

				break;
			case 'um_review_add':
				//add review
				$review_id = wp_insert_post( array(
					'post_title'    => $output['title'],
					'post_content'  => $output['content'],
					'post_type'     => 'um_review',
					'post_status'   => 'publish',
					'post_author'   => $user_id,
				) );

				update_post_meta( $review_id, '_reviewer_id', $reviewer_id );
				update_post_meta( $review_id, '_user_id', $user_id );
				update_post_meta( $review_id, '_status', $can_publish_review );
				update_post_meta( $review_id, '_rating', $output['rating'] );


				if ( $can_publish_review ) {
					$this->publish_review( $review_id );
				} else {
					$output['pending'] = __( 'This review will be moderated by an admin before it is live.', 'um-reviews' );
				}

				// send a mail notification
				um_fetch_user( $user_id );
				$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url() );

				$reviewer = get_userdata( $reviewer_id );
				$reviewer_name = $reviewer->display_name;

				UM()->mail()->send( um_user( 'user_email' ), 'review_notice', array(
					'plain_text' => 1,
					'path' => um_reviews_path . 'templates/email/',
					'tags' => array(
						'{rating}',
						'{reviews_link}',
						'{reviewer}',
						'{review_content}'
					),
					'tags_replace' => array(
						sprintf( __( '%s star', 'um-reviews' ), $output['rating'] ),
						$reviews_url,
						$reviewer_name,
						stripslashes( $content )
					)
				) );

				// reviewed, reviewer (ID and display name), and reviews link
				do_action( 'um_review_is_published', $user_id, $reviewer_id, $reviewer_name, $reviews_url, $output['title'], $review_id );
				break;
		}

		wp_send_json_success( $output );
	}


	/**
	 * Delete review
	 */
	function ajax_review_trash() {
		UM()->check_ajax_nonce();

		$review_id = filter_input( INPUT_POST, 'review_id', FILTER_SANITIZE_NUMBER_INT );
		$reviewer_id = get_post_meta( $review_id, '_reviewer_id', true );
		$output = array();

		if ( ! $this->can_remove( $reviewer_id ) ) {
			$output['message'] = 'error';
			wp_send_json_error( $output );
		}

		$user_id = get_post_meta( $review_id, '_user_id', true );
		$deleted = wp_delete_post( $review_id, true );
		$this->adjust_user_rating( $user_id );

		if ( $deleted ) {
			$output['message'] = 'success';
			wp_send_json_success( $output );
		} else {
			$output['message'] = 'error';
			wp_send_json_error( $output );
		}
	}


	/**
	 * flag a review
	 */
	function ajax_review_flag() {
		UM()->check_ajax_nonce();

		/**
		 * @var $review_id
		 */
		extract( $_POST );

		update_post_meta( $review_id, '_flagged', 1 );

		$output['response'] = __( 'This review has been flagged for admin review', 'um-reviews' );

		wp_send_json_success( $output );
	}


	/**
	 * Post new / Update old replies
	 */
	function ajax_reply_post() {
		$nonce_name = 'um_reviews_reply_send';
		$update = false;

		if ( isset( $_POST['reply_id'] ) ) {
			$nonce_name = 'um_reviews_reply_update_' . intval( $_POST['reply_id'] );
			$update= true;
		}
		
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $nonce_name ) ) {
			wp_send_json_error( __( 'Invalid nonce', 'um-reviews' ) );
		}
		
		if ( trim( $_POST['content'] ) == '' ) {
			wp_send_json_error( __( 'Reply content is required.', 'um-reviews' ) );
		}


		$user_id = absint( $_POST['profile_id'] );
		$review_id = absint( $_POST['review_id'] );
		$content = esc_html( $_POST['content'] );
		um_fetch_user( $user_id );

		if ( $update ) {
			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_comment( $reply_id );

			if ( $reply && $reply->user_id == $user_id ) {

				$updated = wp_update_comment( array(
					'comment_ID'      => $reply_id,
					'comment_content' => $content
				) );

			}

			if ( ! isset( $updated ) ) {
				wp_send_json_error( __( 'Wrong reply update process.', 'um-reviews' ) );
			}

			wp_send_json_success( __( 'Reply updated', 'um-reviews' ) );

		} else{

			$reply_id = wp_insert_comment( array(
				'comment_author'        => um_user( 'display_name' ),
				'comment_content'       => $content,
				'comment_post_ID'       => $review_id,
				'comment_author_email'  => um_user( 'user_email' ),
				'comment_author_url'    => '',
				'comment_type'          => 'um-reviews',
				'user_id'               => $user_id
			) );
			$reply = get_comment( $reply_id );

			/* >> review reply notification */
			$user = get_userdata( $user_id );
			$review = get_post( $review_id );
			$reviewer = get_userdata( $review->_reviewer_id );
			$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url( $user_id ) );

			UM()->mail()->send( $reviewer->user_email, 'review_reply_notice', array(
				'plain_text' => 1,
				'path' => um_reviews_path . 'templates/email/',
				'tags' => array(
					'{reviews_link}',
					'{user}',
					'{reviewer}',
					'{review_content}',
					'{reply_content}'
				),
				'tags_replace' => array(
					$reviews_url,
					$user->display_name,
					$reviewer->display_name,
					stripslashes( $review->post_content ),
					stripslashes( $content )
				)
			) );
			/* << review reply notification */

			$args = array(
				'review_id' => $review_id,
				'reviewer_id' =>  get_post_meta( $review_id, '_reviewer_id', true ),
				'profile_id' => $user_id,
				'r' => $reply
			);
			$html = UM()->get_template( 'reply-list.php', um_reviews_plugin, $args );
		} // new reply - endif
		
		wp_send_json_success( $html );
	}
	
	
	/* Delete reply */
	
	function ajax_reply_delete() {
		$reply_id = absint( $_POST['reply_id'] );
		$reply = get_comment( $reply_id );
		if ( $reply && $reply->user_id == get_current_user_id() ) {
			wp_delete_comment( $reply_id );
		}

		wp_send_json_success( 'success' );
	}


	//class end
}