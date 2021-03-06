<?php
namespace um_ext\um_instagram\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Instagram_Setup {

	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Instagram_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'enable_instagram_photo' => 0,
			'instagram_photo_api_version' => 'instagram',
			'instagram_photo_app_id' => '',
			'instagram_photo_app_secret' => '',
			'instagram_photo_client_id' => '',
			'instagram_photo_client_secret' => '',
		);
	}


	/**
	 *
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
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}