<?php
namespace um_ext\um_bbpress\core;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Class bbPress_Setup
 * @package um_ext\um_bbpress\core
 */
class bbPress_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * bbPress_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'profile_tab_forums'           => 1,
			'profile_tab_forums_privacy'   => 0,
		);

		$notification_types = array();
		$notification_types['bbpress_user_reply'] = array(
			'title'         => __('User leaves a reply to bbpress topic','um-bbpress'),
			'template'      => '<strong>{member}</strong> has <strong>replied</strong> to a topic you started on the forum.',
			'account_desc'  => __('When a member replies to one of my topics','um-bbpress'),
		);

		$notification_types['bbpress_guest_reply'] = array(
			'title'         => __('Guest leaves a reply to bbpress topic','um-bbpress'),
			'template'      => 'A guest has <strong>replied</strong> to a topic you started on the forum.',
			'account_desc'  => __('When a guest replies to one of my topics','um-bbpress'),
		);

		foreach ( $notification_types as $k => $desc ) {
			$this->settings_defaults['log_' . $k] = 1;
			$this->settings_defaults['log_' . $k . '_template'] = $desc['template'];
		}
	}


	/**
	 *
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
	 *
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}