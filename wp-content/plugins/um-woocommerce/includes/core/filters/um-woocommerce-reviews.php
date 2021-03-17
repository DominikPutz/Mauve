<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@Link review owner with UM profile
	***/
	add_filter( 'comment_author', 'um_woocommerce_comment_author', 100, 2 );
	function um_woocommerce_comment_author( $author, $comment_ID ) {
		global $comment;

		$return = $author;
		$comment = get_comment( $comment_ID );
		if ( isset( $comment->user_id ) && !empty( $comment->user_id ) ) {
			
			if ( isset( UM()->user()->cached_user[ $comment->user_id ] ) && UM()->user()->cached_user[ $comment->user_id ] ) {
				
				$return = '<a href="'. UM()->user()->cached_user[$comment->user_id]['url'] . '">' . UM()->user()->cached_user[$comment->user_id]['name'] . '</a>';
			
			} else {
				
				um_fetch_user($comment->user_id);
				UM()->user()->cached_user[ $comment->user_id ] = array('url' => um_user_profile_url(), 'name' => um_user('display_name') );
				$return = '<a href="'. UM()->user()->cached_user[$comment->user_id]['url'] . '">' . UM()->user()->cached_user[$comment->user_id]['name'] . '</a>';
				um_reset_user();
				
			}
			
		}
		return $return;
	}