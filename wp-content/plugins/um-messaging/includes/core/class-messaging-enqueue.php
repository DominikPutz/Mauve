<?php
namespace um_ext\um_messaging\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Messaging_Enqueue {


	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9999 );
	}


	/**
	 * Frontend Scripts
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um-messaging-moment', um_messaging_url . 'assets/js/moment-with-locales.min.js', array( 'jquery' ), um_messaging_version, true );
		wp_register_script( 'um-messaging-moment-timezone', um_messaging_url . 'assets/js/moment-timezone.min.js', array( 'jquery' ), um_messaging_version, true );
		wp_register_script( 'um-messaging-autosize', um_messaging_url . 'assets/js/autosize.min.js', array( 'jquery' ), um_messaging_version, true );

		wp_register_script( 'um-messaging', um_messaging_url . 'assets/js/um-messaging' . $suffix . '.js', array( 'jquery', 'wp-util', 'jquery-ui-datepicker', 'um-messaging-moment', 'um-messaging-moment-timezone', 'um-messaging-autosize', 'um_scripts' ), um_messaging_version, true );

		// Localize the script with new data
		wp_localize_script( 'um-messaging', 'um_message_i18n', array(
			'no_chats_found' => __( 'No chats found here', 'um-messaging' ),
		) );

		// Localize time
		wp_localize_script( 'um-messaging', 'um_message_timezone', array(
			'string' => get_option( 'timezone_string' ),
			'offset' => get_option( 'gmt_offset' ),
		) );

		$interval = UM()->options()->get( 'pm_coversation_refresh_timer' );
		$interval = ( ! empty( $interval ) && is_numeric( $interval ) ) ? $interval * 1000 : 5000;

		$can_read = false;

		if ( is_user_logged_in() ) {
			um_fetch_user( get_current_user_id() );

			if ( um_user( 'can_read_pm' ) ) {
				$can_read = true;
			}

			um_reset_user();
		}

		wp_localize_script( 'um-messaging', 'um_messages', array(
			'can_read' => $can_read,
			'interval' => $interval
		) );

		wp_register_style( 'um-messaging', um_messaging_url . 'assets/css/um-messaging' . $suffix . '.css', array('um_scrollbar'), um_messaging_version );

		$color_hex = UM()->options()->get( 'pm_active_color' );
		$color_rgb = UM()->Messaging_API()->api()->hex_to_rgb( $color_hex );

		$css = '
			.um-message-item-content a { color:' . $color_hex . '; text-decoration: underline !important;}
			.um-message-item-content a:hover {color: rgba(' . $color_rgb . ', 0.9);}
			.um-message-item.left_m .um-message-item-content a {color: #fff}
			.um-message-send, .um-message-send.disabled:hover { background-color:' . $color_hex . '; }
			.um-message-send:hover { background-color: rgba(' . $color_rgb . ', 0.9) }
			.um-message-item.left_m .um-message-item-content { background-color: rgba(' . $color_rgb . ', 0.8);}
			.um-message-footer { background: rgba(' . $color_rgb . ', 0.03); border-top: 1px solid rgba(' . $color_rgb . ', 0.2);}
			.um-message-textarea textarea, div.um div.um-form .um-message-textarea textarea {border: 2px solid rgba(' . $color_rgb . ', 0.3) !important}
			.um-message-textarea textarea:focus,  div.um div.um-form .um-message-textarea textarea:focus {border: 2px solid rgba(' . $color_rgb . ', 0.6) !important}
			.um-message-emolist { border: 1px solid rgba(' . $color_rgb . ', 0.25);}
			.um-message-conv-item.active {color: ' . $color_hex . ';}
			.um-message-conv-view {border-left: 1px solid rgba(' . $color_rgb . ', 0.2);}
		';

		wp_add_inline_style( 'um-messaging', wp_strip_all_tags( $css ) );
	}

}