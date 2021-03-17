<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Setup
 *
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Setup {

	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * User_Photos_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'um_user_photos_albums_column'  => '',
			'um_user_photos_images_column'  => '',
			'um_user_photos_cover_size'     => '',
			'um_user_photos_image_size'     => '',
			'profile_tab_photos'            => 1,
				
			'new_album_on' => 1,
			'new_album_sub' => '[{site_name}] User Photo - Album {album_action}',
			'new_album' => 'User "{user_name}" {album_action} album "{album_title}".<br />Click on the following link to see his/her albums:<br />{profile_url}',
		);
	}


	/**
	 * Set default settings function
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[$key] ) ) {
				$options[$key] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run User Photos Setup
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}