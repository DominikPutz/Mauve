<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Profile
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Profile {


	/**
	 * Bookmark_Profile constructor.
	 */
	function __construct() {
		add_filter( 'um_profile_tabs', array( $this, 'add_profile_tab' ), 801 );
		add_filter( 'um_user_profile_tabs', array( &$this, 'add_user_tab' ), 5, 1 );
		add_action( 'um_profile_content_bookmarks_default', array( $this, 'get_bookmarks_content' ) );
		add_action( 'um_profile_content_bookmarks_folders',  array( $this, 'get_bookmarks_content' ) );
		add_action( 'um_profile_content_bookmarks_all', array( $this, 'get_bookmarks_content_all' ) );
	}


	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_profile_tab( $tabs ) {
		$tabs['bookmarks'] = array(
			'name'  => __( 'Bookmarks', 'um-user-bookmarks' ),
			'icon'  => 'um-faicon-bookmark',
		);

		return $tabs;
	}


	/**
	 * Hide tab if there isn't capability
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_user_tab( $tabs ) {
		if ( empty( $tabs['bookmarks'] ) ) {
			return $tabs;
		}

		if ( ! UM()->User_Bookmarks()->user_can_view_bookmark( um_profile_id() ) ) {
			unset( $tabs['bookmarks'] );
		} else {
			$tabs['bookmarks']['subnav'] = array(
				'folders'   => UM()->User_Bookmarks()->get_folder_text( true ),
				'all'       => __( 'All', 'um-user-bookmarks' )
			);
			$tabs['bookmarks']['subnav_default'] = 'folders';
		}

		return $tabs;
	}


	/**
	 *
	 */
	function get_bookmarks_content() {
		wp_enqueue_script( 'um-user-bookmarks' );
		wp_enqueue_style( 'um-user-bookmarks' );

		if ( UM()->User_Bookmarks()->user_can_view_bookmark( um_profile_id() ) ) {
			echo $this->get_user_profile_bookmarks();
		} else {
			_e( 'You do not have permission to view bookmark', 'um-user-bookmarks' );
		}
	}


	/**
	 *
	 */
	function get_bookmarks_content_all() {
		wp_enqueue_script( 'um-user-bookmarks' );
		wp_enqueue_style( 'um-user-bookmarks' );

		if ( UM()->User_Bookmarks()->user_can_view_bookmark( um_profile_id() ) ) {
			echo $this->get_user_profile_bookmarks_all();
		} else {
			_e( 'You do not have permission to view bookmark', 'um-user-bookmarks' );
		}
	}


	/**
	 * Returns folder view of user bookmarks
	 *
	 * @param null $profile_id
	 *
	 * @return false|string
	 */
	function get_user_profile_bookmarks( $profile_id = null ) {
		if ( ! $profile_id ) {
			$profile_id = um_profile_id();
		}

		$include_private = false;
		if ( is_user_logged_in() && get_current_user_id() == $profile_id ) {
			$include_private = true;
		}

		$user_bookmarks = get_user_meta( $profile_id , '_um_user_bookmarks' , true );

		wp_enqueue_script( 'um-user-bookmarks' );
		wp_enqueue_style( 'um-user-bookmarks' );

		ob_start();

		// Load view
		UM()->get_template( 'profile/folder-view.php', um_user_bookmarks_plugin, array(
			'user_bookmarks'   => $user_bookmarks,
			'include_private'  => $include_private,
			'profile_id'       => $profile_id
		), true );

		$html = ob_get_clean();
		return $html;
	}


	/**
	 * Retrieves all bookmark posts for a user
	 *
	 * @param null|int $profile_id
	 *
	 * @return string
	 */
	function get_user_profile_bookmarks_all( $profile_id = null ) {
		wp_enqueue_script( 'um-user-bookmarks' );
		wp_enqueue_style( 'um-user-bookmarks' );

		if ( ! $profile_id ) {
			$profile_id = um_profile_id();
		}
		
		$include_private = false;
		if ( is_user_logged_in() ) {
			if ( get_current_user_id() == intval( $profile_id ) ) {
				$include_private = true;
			}
		}

		$user_bookmarks = get_user_meta( $profile_id, '_um_user_bookmarks' , true );
		if ( ! $user_bookmarks && ! count( $user_bookmarks ) ) {
			return __( 'No bookmarks have been added.', 'um-user-bookmarks' );
		}

		$bookmarks = array();
		$count = 0;
		if ( $user_bookmarks ) {
			foreach ( $user_bookmarks as $key => $value ) {
				if ( ! $include_private && $value['type'] == 'private' ) {
					continue;
				}

				if ( ! isset( $value['bookmarks'] ) || empty( $value['bookmarks'] ) ) {
					continue;
				}

				$bookmarks = array_merge( $bookmarks, array_keys( $value['bookmarks'] ) );
				$count ++;
			}
		}
		
		ob_start();
		
		if ( $count ) {
			UM()->get_template( 'profile/bookmarks.php', um_user_bookmarks_plugin, array( 'bookmarks' => $bookmarks ), true );
		} else {
			echo __( 'No bookmarks have been added.', 'um-user-bookmarks' );
		}

		$html = ob_get_clean();
		return $html;
	}
}