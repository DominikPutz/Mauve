<?php
namespace um_ext\um_messaging\admin\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Messaging_Admin
 * @package um_ext\um_messaging\admin\core
 */
class Messaging_Admin {


	/**
	 * Messaging_Admin constructor.
	 */
	function __construct() {
		add_filter( 'um_admin_role_metaboxes', array( &$this, 'role_metabox' ), 10, 1 );
		add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ), 10, 1 );
	}


	/**
	 * Creates options in Role page
	 *
	 * @param array $roles_metaboxes
	 *
	 * @return array
	 */
	function role_metabox( $roles_metaboxes ) {

		$roles_metaboxes[] = array(
			'id'        => "um-admin-form-messaging{" . um_messaging_path . "}",
			'title'     => __( 'Private Messages', 'um-messaging' ),
			'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
			'screen'    => 'um_role_meta',
			'context'   => 'normal',
			'priority'  => 'default'
		);

		return $roles_metaboxes;
	}


	/**
	 * Extend UM settings
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function extend_settings( $settings ) {

		$settings['licenses']['fields'][] = array(
			'id'        => 'um_messaging_license_key',
			'label'     => __( 'Private Messaging License Key', 'um-messaging' ),
			'item_name' => 'Private Messages',
			'author'    => 'Ultimate Member',
			'version'   => um_messaging_version,
		);

		$key = ! empty( $settings['extensions']['sections'] ) ? 'messaging' : '';
		$settings['extensions']['sections'][ $key ] = array(
			'title'     => __( 'Private Messaging', 'um-messaging' ),
			'fields'    => array(
				array(
					'id'        => 'show_pm_button',
					'type'      => 'checkbox',
					'label'     => __( 'Show messages button in member directory', 'um-messaging' ),
				),
				array(
					'id'        => 'pm_unread_first',
					'type'      => 'checkbox',
					'label'     => __( 'Show unread messages first', 'um-messaging' ),
				),
				array(
					'id'        => 'pm_char_limit',
					'type'      => 'text',
					'label'     => __( 'Message character limit', 'um-messaging' ),
					'validate'  => 'numeric',
					'size'      => 'small',
				),
				array(
					'id'        => 'pm_block_users',
					'type'      => 'text',
					'label'     => __( 'Block users from sending/receiving messages', 'um-messaging' ),
					'tooltip'   => __( 'A comma seperated list of user IDs that cannot send/receive messages on the site.', 'um-messaging' ),
					'size'      => 'medium',
				),
				array(
					'id'            => 'pm_active_color',
					'type'          => 'color',
					'label'         => __( 'Primary color', 'um-messaging' ),
					'validate'      => 'color',
					'transparent'   => false,
				),
				array(
					'id'        => 'pm_coversation_refresh_timer',
					'type'      => 'text',
					'label'     => __( 'How often do you want the AJAX refresh conversation (in seconds)', 'um-messaging' ),
					'validate'  => 'numeric',
					'size'      => 'small',
				),
				array(
					'id'            => 'pm_notify_period',
					'type'          => 'select',
					'label'         => __( 'Send email notifications If user did not login for', 'um-messaging' ),
					'tooltip'       => __( 'Send email notifications about new messages if the user\'s last login time exceeds that period.', 'um-messaging' ),
					'options'       => array(
						3600    => __( '1 Hour', 'um-messaging' ),
						86400   => __( '1 Day', 'um-messaging' ),
						604800  => __( '1 Week', 'um-messaging' ),
						2592000 => __( '1 Month', 'um-messaging' ),
					),
					'placeholder'   => __( 'Select...', 'um-messaging' ),
					'size'          => 'small',
				),
				array(
					'id'            => 'pm_remind_period',
					'type'					=> 'text',
					'label'         => __( 'Send email notifications If user didn\'t read message for [n] hours', 'um-messaging' ),
					'tooltip'       => __( 'Send email notifications about unread message if the user didn\'t read it during that period.', 'um-messaging' ),
					'placeholder'   => __( '[n] hours', 'um-messaging' ),
					'validate'  => 'numeric',
					'size'      => 'small',
				),
				array(
					'id'            => 'pm_remind_limit',
					'type'					=> 'text',
					'label'         => __( 'Send email notifications not more then [m] times.', 'um-messaging' ),
					'tooltip'       => __( 'Email notifications about unread message will be send every [n] hours but no more then [m] times.', 'um-messaging' ),
					'placeholder'   => __( '[m] times', 'um-messaging' ),
					'validate'			=> 'numeric',
					'size'					=> 'small',
					'max'						=> 9,
				)
			)
		);

		return $settings;
	}
}