<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Setup
 *
 * @package um_ext\um_user_bookmarks\core
 */
class Bookmark_Setup {

	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Bookmark_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'profile_tab_bookmarks'             => true,
			'um_user_bookmarks_post_types'      => array( 'post', 'page' ),
			'um_user_bookmarks_archive_page'    => 0,
			'um_user_bookmarks_position'        => 'bottom',
			'um_user_bookmarks_add_text'        => __( 'Bookmark', 'um-user-bookmarks' ),
			'um_user_bookmarks_remove_text'     => __( 'Remove bookmark', 'um-user-bookmarks' ),
			'um_user_bookmarks_folders_text'    => __( 'Folders', 'um-user-bookmarks' ),
			'um_user_bookmarks_folder_text'     => __( 'Folder', 'um-user-bookmarks' ),
			'um_user_bookmarks_bookmarked_icon' => 'um-faicon-bookmark',
			'um_user_bookmarks_regular_icon'    => 'um-faicon-bookmark-o',
		);
	}


	/**
	 * Set default settings function
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run User Bookmark Setup
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}