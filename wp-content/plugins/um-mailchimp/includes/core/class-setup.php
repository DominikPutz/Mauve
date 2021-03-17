<?php
namespace um_ext\um_mailchimp\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class for plugin activation
 *
 * @example UM()->classes['um_mailchimp_setup']
 * @example UM()->Mailchimp()->setup()
 */
class Setup {


	/**
	 * Settings
	 *
	 * @var array
	 */
	public $settings_defaults;


	/**
	 * Class constructor
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'mailchimp_api'                 => '',
			'mailchimp_unsubscribe_delete'  => 0,
			'mailchimp_allow_add_tags'      => 1,
			'mailchimp_double_optin'        => 0,
			'mailchimp_enable_cache'        => 1,
			'mailchimp_transient_time'      => 600,
			'mailchimp_enable_log'          => 0,
			'mailchimp_enable_log_response' => 0,
		);
	}


	/**
	 * Set Settings
	 */
	private function set_default_settings() {
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
	 * Run on plugin activation
	 */
	public function run_setup() {
		$this->set_default_settings();
	}

}