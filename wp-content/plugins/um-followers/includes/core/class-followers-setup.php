<?php
namespace um_ext\um_followers\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Setup
 * @package um_ext\um_followers\core
 */
class Followers_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Followers_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'followers_show_stats'              => 1,
			'followers_show_button'             => 1,
			'followers_allow_admin_to_follow'   => 0,
			'new_follower_on'                   => 1,
			'new_follower_sub'                  => '{follower} is now following you on {site_name}!',
			'new_follower'                      => 'Hi {followed},<br /><br />' .
				'{follower} has just followed you on {site_name}.<br /><br />' .
				'View his/her profile:<br />' .
				'{follower_profile}<br /><br />' .
				'Click on the following link to see your followers:<br />' .
				'{followers_url}<br /><br />' .
				'This is an automated notification from {site_name}. You do not need to reply.',
		);

		$notification_types['new_follow'] = array(
			'title'         => __( 'User get followed by a person', 'um-followers' ),
			'template'      => '<strong>{member}</strong> has just followed you!',
			'account_desc'  => __( 'When someone follows me', 'um-followers' ),
		);

		foreach ( $notification_types as $k => $desc ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $desc['template'];
		}
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
	 * sql setup
	 */
	function sql_setup() {
		global $wpdb;

		if ( get_option( 'ultimatemember_followers_db' ) == um_followers_version ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}um_followers (
id int(11) unsigned NOT NULL auto_increment,
time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
user_id1 int(11) unsigned NOT NULL,
user_id2 int(11) unsigned NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'ultimatemember_followers_db', um_followers_version );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->sql_setup();
		$this->set_default_settings();
	}
}