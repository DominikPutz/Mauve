<?php
namespace um_ext\um_mycred\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class myCRED_Setup {
	var $settings_defaults;

	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'mycred_badge_size'             => 80,
			'account_tab_points'            => 1,
			'mycred_refer'                  => 0,
			'mycred_show_badges_in_header'  => 0,
			'mycred_show_badges_in_members' => 0,
			'mycred_decimals'               => 0,
			'mycred_hide_role'              => 0,
			'mycred_show_bb_rank'           => 0,
			'mycred_show_bb_points'         => 0,
			'mycred_show_bb_progress'       => 0,
			'profile_tab_badges'           => 1,
			'profile_tab_badges_privacy'   => 0,
		);


		$notification_types['mycred_custom_notification'] = array(
			'title' => 'myCRED notification',
			'template' => '',
			'account_desc' => '',
		);

		$notification_types['mycred_award'] = array(
			'title' => __('User awarded points for action','um-mycred'),
			'template' => __('You have received <strong>{mycred_points}</strong> for <strong>{mycred_task}</strong>','um-mycred'),
			'account_desc' => __('When I receive points by completing an action','um-mycred'),
		);

		$notification_types['mycred_deduct'] = array(
			'title' => __('User deducted points for action','um-mycred'),
			'template' => __('<strong>{mycred_points}</strong> deduction for <strong>{mycred_task}</strong>','um-mycred'),
			'account_desc' => __('Points deducted when incompleted an action','um-mycred'),
		);

		$notification_types['mycred_points_sent'] = array(
			'title' => __('User receives points from another person','um-mycred'),
			'template' => __('You have just got <strong>{mycred_points}</strong> from <strong>{mycred_sender}</strong>','um-mycred'),
			'account_desc' => __('When I receive points balance from another member','um-mycred'),
		);

		foreach ( $notification_types as $k => $desc ) {
			$this->settings_defaults['log_' . $k] = 1;
			$this->settings_defaults['log_' . $k . '_template'] = $desc['template'];
		}
	}


	function set_default_settings() {
		$options = get_option( 'um_options' );
		$options = empty( $options ) ? array() : $options;

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[$key] ) )
				$options[$key] = $value;

		}

		update_option( 'um_options', $options );
	}


	function run_setup() {
		$this->set_default_settings();
	}

}