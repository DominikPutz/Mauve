<?php
namespace um_ext\um_messaging\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Messaging_Account
 * @package um_ext\um_messaging\core
 */
class Messaging_Account {


	/**
	 * Messaging_Account constructor.
	 */
	function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ), 10, 1 );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 46, 2 );
	}


	/**
	 * Update Account action
	 */
	function account_update() {
		/**
		 * issue helpscout#31301
		 */
		$current_tab = filter_input( INPUT_POST, '_um_account_tab' );
		if( 'notifications' !== $current_tab ){
			return;
		}

		$user_id = um_user( 'ID' );

		if ( isset( $_POST['_enable_new_pm'] ) ) {
			update_user_meta( $user_id, '_enable_new_pm', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_pm', 'no' );
		}
	}



	/**
	 * Add Notifications tab to account page
	 *
	 * @param array $tabs
	 * @return array
	 */
	function account_notification_tab( $tabs ) {

		if ( empty( $tabs[400]['notifications'] ) ) {
			$tabs[400]['notifications'] = array(
				'icon'          => 'um-faicon-envelope',
				'title'         => __( 'Notifications', 'um-messaging' ),
				'submit_title'  => __( 'Update Notifications', 'um-messaging' ),
			);
		}

		return $tabs;
	}


	/**
	 * Show a notification option in email tab
	 *
	 *
	 * @param string $output
	 * @param array $shortcode_args
	 * @return string
	 */
	function account_tab( $output, $shortcode_args ) {
		if ( isset( $shortcode_args['_enable_new_pm'] ) && 0 == $shortcode_args['_enable_new_pm'] ) {
			return $output;
		}

		UM()->account()->add_displayed_field( '_enable_new_pm', 'notifications' );

		$output .= UM()->get_template( 'account_notifications.php', um_messaging_plugin, array(
			'_enable_new_pm' => UM()->Messaging_API()->api()->enabled_email( get_current_user_id() ),
		) );
		
		return $output;
	}

}