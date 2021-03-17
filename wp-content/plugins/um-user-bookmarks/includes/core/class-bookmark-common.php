<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Common
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Common {

	/**
	 * Bookmark_Common constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_filter( 'the_content', array( $this, 'add_um_user_bookmarks_button' ) );
		add_action( 'template_redirect', array( $this, 'add_um_user_bookmarks_button_for_product' ) );

		add_action( 'um_delete_user', array( $this, 'delete_posts_meta' ), 10, 1 );

		add_action( 'before_delete_post', array( $this, 'delete_user_bookmarks' ), 10, 1 );
		
		/** 
		 * Get "Bookmark" template for the post 
		 * 
		 * @example do_action( 'um_bookmarks_button', null, true );
		 */
		add_action( 'um_bookmarks_button', array( $this, 'get_bookmarks_button' ), 10, 2 );
	}


	/**
	 * Load modal window on profile form for select Unsplash image
	 */
	function modal_area() {
		UM()->get_template( 'modal.php', um_user_bookmarks_plugin, array(), true );
	}


	/**
	 *
	 */
	function wp_enqueue_scripts() {
		wp_register_script( 'um-user-bookmarks', um_user_bookmarks_url . 'assets/js/um-user-bookmarks' . UM()->enqueue()->suffix . '.js' , array( 'jquery', 'wp-util', 'wp-i18n' ) , um_user_bookmarks_version , true );
		wp_set_script_translations( 'um-user-bookmarks', 'um-user-bookmarks' );

		wp_register_style( 'um-user-bookmarks', um_user_bookmarks_url . 'assets/css/um-user-bookmarks' . UM()->enqueue()->suffix . '.css' , array( 'um_fonticons_fa' ), um_user_bookmarks_version );
	}


	/**
	 * Show bookmark and remore bookmark button in single post type page
	 *
	 * @param $content
	 *
	 * @return string
	 */
	function add_um_user_bookmarks_button( $content ) {
		if( !is_user_logged_in() || !um_user( 'enable_bookmark' ) ) {
			return $content;
		}
		if( !is_singular() && !(is_archive() && UM()->options()->get( 'um_user_bookmarks_archive_page' ) ) ) {
			return $content;
		}

		global $post;
		$user_id = get_current_user_id();
		$post_types = UM()->options()->get( 'um_user_bookmarks_post_types' );
		$bookmark_position = UM()->options()->get( 'um_user_bookmarks_position' );

		if ( $post->post_type != 'product' && in_array( $post->post_type, $post_types ) && ! is_ultimatemember() && ! UM()->User_Bookmarks()->is_post_disabled( $post->ID ) ) {

			wp_enqueue_script( 'um-user-bookmarks' );
			wp_enqueue_style( 'um-user-bookmarks' );

			if ( ! UM()->User_Bookmarks()->is_bookmarked( $user_id, $post->ID ) ) {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'add', $post->ID ) . '</p>';
			} else {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'remove', $post->ID ) . '</p>';
			}

			if ( $bookmark_position == 'bottom' ) {
				$content .= $button;
			} elseif ( $bookmark_position == 'top' ) {
				$content = $button . $content;
			}

			add_action( 'wp_footer', array( &$this, 'modal_area' ) );
		}

		return $content;
	}


	/**
	 * Show bookmark and remove bookmark button in single WC product
	 */
	function add_um_user_bookmarks_button_for_product() {
		if ( ! ( is_user_logged_in() && is_singular('product') && um_user( 'enable_bookmark' ) ) ) {
			return;
		}

		global $post;
		$user_id = get_current_user_id();
		$post_types = UM()->options()->get( 'um_user_bookmarks_post_types' );
		$bookmark_position = UM()->options()->get( 'um_user_bookmarks_position' );

		if ( in_array( 'product', $post_types ) && ! is_ultimatemember() && ! UM()->User_Bookmarks()->is_post_disabled( $post->ID ) ) {

			wp_enqueue_script( 'um-user-bookmarks' );
			wp_enqueue_style( 'um-user-bookmarks' );

			if ( ! UM()->User_Bookmarks()->is_bookmarked( $user_id , $post->ID ) ) {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'add', $post->ID ) . '</p>';
			} else {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'remove', $post->ID ) . '</p>';
			}

			add_action( 'wp_footer', array( &$this, 'modal_area' ) );

			if ( $bookmark_position == 'bottom' ) {

				add_action( 'woocommerce_after_single_product', function() use( $button ) {
					echo $button;
				});

			} elseif ( $bookmark_position == 'top' ) {

				add_action( 'woocommerce_before_single_product', function() use( $button ) {
					echo $button;
				});

			}
		}
	}


	/**
	 * Delete post meta with bookmarks when user is deleted
	 * @param int $user_id
	 */
	function delete_posts_meta( $user_id ) {
		$posts = get_posts( array(
			'numberposts' => '-1',
			'post_type' => 'any',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => '_um_in_users_bookmarks',
					'value' => serialize( strval( $user_id ) ),
					'compare' => 'LIKE',
				),
				array(
					'key' => '_um_in_users_bookmarks',
					'value' => serialize( intval( $user_id ) ),
					'compare' => 'LIKE',
				)
			),
			'fields' => 'ids',
		) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post_id ) {
				$post_users = get_post_meta( $post_id, '_um_in_users_bookmarks', true );
				$post_users = empty( $post_users ) ? array() : $post_users;

				if ( $k = array_search( $user_id, $post_users ) ) {
					unset( $post_users[ $k ] );
					$post_users = array_unique( $post_users );
				}

				update_post_meta( $post_id, '_um_in_users_bookmarks', $post_users );
			}
		}
	}


	/**
	 * Delete User Bookmarks when post is deleted
	 *
	 * @param int $post_id
	 */
	function delete_user_bookmarks( $post_id ) {
		$post_users = get_post_meta( $post_id, '_um_in_users_bookmarks', true );
		
		if ( ! empty( $post_users ) && is_array( $post_users ) ) {

			foreach ( $post_users as $user_id ) {
				$user_bookmarks = get_user_meta( $user_id, '_um_user_bookmarks', true );

				foreach ( $user_bookmarks as $folder_key => $folder ) {
					if ( empty( $folder['bookmarks'] ) ) {
						continue;
					}

					if ( isset( $folder['bookmarks'][ $post_id ] ) ) {
						unset( $user_bookmarks[ $folder_key ]['bookmarks'][ $post_id ] );
					}
				}

				update_user_meta( $user_id, '_um_user_bookmarks', $user_bookmarks );
			}

		}
	}


	/**
	 * Get "Bookmark" template for the post
	 *
	 * @global WP_User $current_user
	 * @global WP_Post $post
	 * @staticvar int $modal_area
	 * @param null|int $post_id - post identifier (current post by default)
	 * @param bool $echo - should function echo template?
	 * @return string HTML - "Add Bookmark" or "Remove Bookmark" template
	 */
	public function get_bookmarks_button( $post_id = null, $echo = true ) {
		global $current_user, $post;
		static $modal_area = 0;

		if( !defined( 'um_user_bookmarks_version' ) || !$current_user ) {
			return;
		}

		if( empty( $post_id ) && is_a( $post, 'WP_Post' ) ) {
			$post_id = $post->ID;
			$post_type = $post->post_type;
		} else {
			$post_type = get_post_type( $post_id );
		}

		$post_disabled = UM()->User_Bookmarks()->is_post_disabled( $post_id );
		$post_types = ( array ) UM()->options()->get( 'um_user_bookmarks_post_types' );

		$button = '';

		if( in_array( $post_type, $post_types ) && !$post_disabled ) {

			wp_enqueue_script( 'um-user-bookmarks' );
			wp_enqueue_style( 'um-user-bookmarks' );

			if( !UM()->User_Bookmarks()->is_bookmarked( $current_user->ID, $post_id ) ) {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'add', $post_id ) . '</p>';
			} else {
				$button = '<p>' . UM()->User_Bookmarks()->get_button( 'remove', $post_id ) . '</p>';
			}

			if( !$modal_area++ ) {
				add_action( 'wp_footer', array( UM()->User_Bookmarks()->common(), 'modal_area' ) );
			}
		}

		if( $button && $echo ) {
			echo $button;
		}

		return $button;
	}
}