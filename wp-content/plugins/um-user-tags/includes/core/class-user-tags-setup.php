<?php
namespace um_ext\um_user_tags\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class User_Tags_Setup {
	var $settings_defaults;
	var $core_form_meta;

	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'user_tags_max_num'         => 5,
			'user_tags_base_directory'  => '',
			'user_tags_slug'            => 'user-tags',
		);
	}


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
	 * Set base directory
	 */
	function set_base_directory() {
		if ( UM()->options()->get( 'members_page' ) ) {
			$member_directory_id = false;

			$page_id = UM()->config()->permalinks['members'];
			if ( ! empty( $page_id ) ) {
				$members_page = get_post( $page_id );
				if ( ! empty( $members_page ) && ! is_wp_error( $members_page ) ) {
					if ( ! empty( $members_page->post_content ) ) {
						preg_match( '/\[ultimatemember[^\]]*?form_id\=[\'"]*?(\d+)[\'"]*?/i', $members_page->post_content, $matches );
						if ( ! empty( $matches[1] ) && is_numeric( $matches[1] ) ) {
							$member_directory_id = $matches[1];
						}
					}
				}
			}

			if ( $member_directory_id ) {
				UM()->options()->update( 'user_tags_base_directory' , $member_directory_id );
			}
		}
	}


	/**
	 * Setup running
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}