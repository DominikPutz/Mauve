<?php
namespace um_ext\um_messaging\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Messaging_Shortcode
 * @package um_ext\um_messaging\core
 */
class Messaging_Shortcode {


	/**
	 * Messaging_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_messages', array( &$this, 'ultimatemember_messages' ) );
		add_shortcode( 'ultimatemember_message_button', array( &$this, 'ultimatemember_message_button' ) );
		add_shortcode( 'ultimatemember_message_count', array( &$this, 'ultimatemember_message_count' ) );
	}


	/**
	 * Conversations list shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_messages( $args = array() ) {
		wp_enqueue_script( 'um-messaging' );
		wp_enqueue_style( 'um-messaging' );

		$defaults = array(
			'user_id' => get_current_user_id()
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 */
		extract( $args );

		$conversations = UM()->Messaging_API()->api()->get_conversations( $user_id );

		if ( ! empty( $conversations ) ) {
			$show_conversations = array();

			foreach ( $conversations as $conversation ) {

				if ( $conversation->user_a == um_profile_id() ) {
					$user = $conversation->user_b;
				} else {
					$user = $conversation->user_a;
				}

				if ( UM()->Messaging_API()->api()->blocked_user( $user ) ) {
					continue;
				}

				if ( UM()->Messaging_API()->api()->hidden_conversation( $conversation->conversation_id ) ) {
					continue;
				}

				$show_conversations[] = $conversation;
			}

			$conversations = $show_conversations;
		} else {
			$conversations = array();
		}


		if ( isset( $_GET['conversation_id'] ) ) {
			if ( absint( sanitize_key( $_GET['conversation_id'] ) ) ) {
				foreach ( $conversations as $conversation ) {
					if ( $conversation->conversation_id == sanitize_key( $_GET['conversation_id'] ) ) {
						$args = array_merge( $args, array( 'current_conversation' => absint( sanitize_key( $_GET['conversation_id'] ) ) ) );
//						$current_conversation = esc_attr( absint( $_GET['conversation_id'] ) );
						continue;
					}
				}
			}
		}

		$args = array_merge( $args, array( 'conversations' => $conversations ) );

		$output = UM()->get_template( 'conversations.php', um_messaging_plugin, $args );

		return $output;
	}


	/**
	 * Start conversation button shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_message_button( $args = array() ) {
		wp_enqueue_script( 'um-messaging' );
		wp_enqueue_style( 'um-messaging' );

		$defaults = array(
			'user_id' => 0,
			'title' => __( 'Message', 'um-messaging' )
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 * @var $title
		 */
		extract( $args );

		if ( empty( $user_id ) || ! UM()->Messaging_API()->api()->can_message( $user_id ) ) {
			return '';
		}

		return UM()->get_template( 'button.php', um_messaging_plugin, $args );
	}


	/**
	 * Unread messages shortcode
	 *
	 * @param array $args
	 *
	 * @return int|string
	 */
	function ultimatemember_message_count( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		wp_enqueue_script( 'um-messaging' );
		wp_enqueue_style( 'um-messaging' );

		$defaults = array(
			'user_id' => get_current_user_id()
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 */
		extract( $args );

		$count = UM()->Messaging_API()->api()->get_unread_count( $user_id );
		$count = ( $count > 10 ) ? 10 . '+' : $count;
		return $count;
	}

}