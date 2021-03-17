<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'UM_User_Bookmarks_Functions' ) ) {


	/**
	 * Class UM_User_Bookmarks_Functions
	 */
	class UM_User_Bookmarks_Functions {


		/**
		 * UM_User_Bookmarks_Functions constructor.
		 */
		function __construct() {
		}


		/**
		 * Check if bookmark is disabled for post
		 *
		 * @param $post_id
		 *
		 * @return bool
		 */
		function is_post_disabled( $post_id ) {
			$post_settings = get_post_meta( $post_id, '_um_user_bookmarks',true );

			if ( ! empty( $post_settings ) ) {
				if ( ! empty( $post_settings['disable_bookmark'] ) ) {
					return true;
				}
			}

			return false;
		}


		/**
		 * Check if post is saved in user bookmarks
		 *
		 * @param int $user_id
		 * @param int $post_id
		 *
		 * @return bool
		 */
		function is_bookmarked( $user_id, $post_id ) {
			$user_bookmarks = get_user_meta( $user_id, '_um_user_bookmarks', true );

			if ( ! empty( $user_bookmarks ) ) {

				foreach ( $user_bookmarks as $key => $value ) {

					if ( ! empty( $value['bookmarks'] ) && isset( $value['bookmarks'][ $post_id ] ) ) {

						return true;

					}

				}

			}

			return false;
		}


		/**
		 * Retrives Add / Remove Bookmark Button for post type single page
		 *
		 * @param $button_type
		 * @param null $post_id
		 *
		 * @return false|string
		 */
		function get_button( $button_type, $post_id = null ) {
			if ( ! $post_id ) {
				global $post;
				$post_id = $post->ID;
			}

			ob_start();

			if ( $button_type == 'add' ) {
				// add bookmark button
				UM()->get_template( 'buttons/add.php', um_user_bookmarks_plugin, array(
					'post_id'   => $post_id,
					'user_id'   => get_current_user_id(),
					'icon'      => UM()->options()->get( 'um_user_bookmarks_regular_icon' ),
					'text'      => UM()->options()->get( 'um_user_bookmarks_add_text' )
				), true );

			} elseif ( $button_type == 'remove' ) {
				// remove bookmark button
				UM()->get_template( 'buttons/remove.php', um_user_bookmarks_plugin, array(
					'post_id'   => $post_id,
					'user_id'   => get_current_user_id(),
					'icon'      => UM()->options()->get( 'um_user_bookmarks_bookmarked_icon' ),
					'text'      => UM()->options()->get( 'um_user_bookmarks_remove_text' )
				), true );
			}

			$content = ob_get_clean();
			return $content;
		}


		/**
		 * Check if user can view bookmakrs
		 *
		 * @param null|int $profile_id
		 *
		 * @return bool
		 */
		function user_can_view_bookmark( $profile_id = null ) {
			$user_id = null;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			if ( ! $profile_id ) {
				return false;
			}

			$privacy = get_user_meta( $profile_id, 'um_bookmark_privacy', true );
			if ( $user_id == intval( $profile_id ) || $privacy == 'everyone' ) {
				return true;
			}

			if ( $privacy == 'only_me' && intval( $user_id ) != intval( $profile_id ) ) {
				return false;
			}

			$custom_privacy = apply_filters( 'um_user_bookmarks_custom_privacy', true, $privacy, $user_id );
			return $custom_privacy;
		}


		/**
		 * Returns text from admin settings
		 *
		 * @param bool $is_plural
		 *
		 * @return string
		 */
		function get_folder_text( $is_plural = false ) {
			$key = $is_plural ? 'um_user_bookmarks_folders_text' : 'um_user_bookmarks_folder_text';
			return UM()->options()->get( $key );
		}
	}
}