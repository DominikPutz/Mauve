<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Shortcode
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Shortcode {


	/**
	 * Bookmark_Shortcode constructor.
	 */
	function __construct() {
		// [um_user_bookmarks user_id=""]
		add_shortcode( 'um_user_bookmarks', array( $this , 'um_user_bookmarks_shortcode_html' ) );

		// [um_bookmarks_button post_id=""]
		if ( ! shortcode_exists( 'um_bookmarks_button' ) ) {
			add_shortcode( 'um_bookmarks_button', array( $this , 'um_bookmarks_button_shortcode' ) );
		}
	}


	/**
	 * Shortcode: "Bookmark" template for the post
	 *
	 * @example [um_bookmarks_button post_id=""]
	 *
	 * @param array $atts
	 *		null|int post_id
	 *
	 * @return string
	 */
	function um_bookmarks_button_shortcode( $atts = array() ) {
		$args = shortcode_atts( array(
			'post_id' => get_the_ID()
		), $atts );

		$button = UM()->User_Bookmarks()->common()->get_bookmarks_button( $args['post_id'], false );

		return $button;
	}


	/**
	 * Shortcode callback to display Bookmark folder view
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	function um_user_bookmarks_shortcode_html( $atts = array() ){
		if ( ! isset( $atts['user_id'] ) && ! is_user_logged_in() ) {
			return '';
		} elseif ( ! isset( $atts['user_id']) && is_user_logged_in() ) {
			$profile_id = get_current_user_id();
		}

		$include_private = false;
		if ( is_user_logged_in() && get_current_user_id() == $profile_id ) {
			$include_private = true;
		}

		$user_bookmarks = get_user_meta( $profile_id, '_um_user_bookmarks',true );
		if ( ! $user_bookmarks ) {
			return __( 'No bookmarks have been added.','um-user-bookmarks' );
		}

		wp_enqueue_script( 'um-user-bookmarks' );
		wp_enqueue_style( 'um-user-bookmarks' );

		ob_start(); ?>

		<div class="um-profile-body bookmarks-default shortcode">

			<?php if ( UM()->User_Bookmarks()->user_can_view_bookmark( $profile_id ) ) {

				// Load plugin view : templates/profile/folder-view.php
				// Load theme view : ultimate-member/bookmarks/profile/folder-view.php
				UM()->get_template( 'profile/folder-view.php', um_user_bookmarks_plugin, array(
					'user_bookmarks'    => $user_bookmarks,
					'include_private'   => $include_private,
					'profile_id'        => $profile_id
				), true );

			} ?>

			<div class="um-clear"></div>

		</div>

		<?php $html = ob_get_clean();
		return $html;
	}
}