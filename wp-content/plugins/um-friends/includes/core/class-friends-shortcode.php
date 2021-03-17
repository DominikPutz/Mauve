<?php
namespace um_ext\um_friends\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Friends_Shortcode
 * @package um_ext\um_friends\core
 */
class Friends_Shortcode {


	/**
	 * Friends_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_friends_online', array( &$this, 'ultimatemember_friends_online' ) );
		add_shortcode( 'ultimatemember_friends', array( &$this, 'ultimatemember_friends' ) );
		add_shortcode( 'ultimatemember_friend_reqs', array( &$this, 'ultimatemember_friend_reqs' ) );
		add_shortcode( 'ultimatemember_friend_reqs_sent', array( &$this, 'ultimatemember_friend_reqs_sent' ) );
		add_shortcode( 'ultimatemember_friends_bar', array( &$this, 'ultimatemember_friends_bar' ) );
	}


	/**
	 * Shortcode "FRIENDS ONLINE"
	 *
	 * @example [ultimatemember_friends_online]
	 *
	 * @param array $args
	 *  'user_id' => {current user ID},
	 *  'style' => 'default',
	 *  'max' => 12
	 *
	 * @return string
	 */
	public function ultimatemember_friends_online( $args = array() ) {

		$defaults = array(
			'user_id'   => um_is_core_page( 'user' ) ? um_profile_id() : get_current_user_id(),
			'style'     => 'default',
			'max'       => 12
		);
		$args = shortcode_atts( $defaults, $args );

		/**
		 * @var $style
		 * @var $user_id
		 */
		extract( $args );

		if ( $style == 'avatars' ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$online_ids = apply_filters( 'um_friends_online_users', array(), $args );
		if ( empty( $online_ids ) ) {
			return '';
		}

		$friends = UM()->Friends_API()->api()->friends( $user_id );
		if ( ! empty( $friends ) ) {
			foreach ( $friends as $k => $v ) {
				if ( empty( array_intersect( $online_ids, array_diff( $v, array( $user_id ) ) ) ) ) {
					unset( $friends[ $k ] );
				}
			}
		}

		if ( empty( $friends ) ) {
			return '';
		}

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = array_merge( $args, array( 'friends' => $friends ) );
		$output = UM()->get_template( "$tpl.php", um_friends_plugin, $t_args );

		return $output;
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_friends_bar( $args = array() ) {

		$defaults = array(
			'user_id' => um_profile_id()
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 */
		extract( $args );

		$can_view = true;
		if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {

			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Friends only', 'um-friends' ) );
			$is_private_case = UM()->user()->is_private_case( $user_id, 'friends' );
			if ( $is_private_case || $is_private_case_old ) { // only friends can view my profile
				$can_view = false;
			}
		}

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( 'args', 'can_view', 'user_id' );
		$output = UM()->get_template( 'friends-bar.php', um_friends_plugin, $t_args );
		
		return $output;
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_friends( $args = array() ) {

		$defaults = array(
			'user_id'   => um_is_core_page('user') ? um_profile_id() : get_current_user_id(),
			'style'     => 'default',
			'max'       => 11
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $style
		 * @var $user_id
		 */
		extract( $args );

		if ( $style == 'avatars' ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$friends = UM()->Friends_API()->api()->friends( $user_id );
		$note = ( $user_id == get_current_user_id() ) ? __( 'You do not have any friends yet.', 'um-friends' ) : __( 'This user does not have any friends yet.', 'um-friends' );
		
		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( 'args', 'friends', 'max', 'note', 'user_id' );
		$output = UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );

		return $output;
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_friend_reqs( $args = array() ) {

		$defaults = array(
			'user_id'   => um_is_core_page('user') ? um_profile_id() : get_current_user_id(),
			'style'     => 'default',
			'max'       => 999
		);
		$args = wp_parse_args( $args, $defaults );
		
		/**
		 * @var $style
		 * @var $user_id
		 */
		extract( $args );

		if ( $style == 'avatars' ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}		

		$_is_reqs = true;
		$friends = UM()->Friends_API()->api()->friend_reqs( $user_id );
		$note = __( 'You do not have pending friend requests yet.', 'um-friends' );
		
		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( '_is_reqs', 'args', 'friends', 'max', 'note', 'user_id' );
		$output = UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );

		return $output;
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_friend_reqs_sent( $args = array() ) {

		$defaults = array(
			'user_id'   => um_is_core_page('user') ? um_profile_id() : get_current_user_id(),
			'style'     => 'default',
			'max'       => 999
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $style
		 * @var $user_id
		 */
		extract( $args );

		if ( $style == 'avatars' ) {
			$tpl = 'friends-mini';
		} else {
			$tpl = 'friends';
		}

		$_sent = true;
		$friends = UM()->Friends_API()->api()->friend_reqs_sent( $user_id );
		$note = __( 'You have not sent any friend requests yet.', 'um-friends' );

		wp_enqueue_script( 'um_friends' );
		wp_enqueue_style( 'um_friends' );

		$t_args = compact( '_sent', 'args', 'friends', 'max', 'note', 'user_id' );
		$output = UM()->get_template( $tpl . '.php', um_friends_plugin, $t_args );

		return $output;
	}
}
