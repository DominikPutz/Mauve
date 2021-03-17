<?php
namespace um_ext\um_reviews\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Reviews_Setup {
	var $settings_defaults;

	function __construct() {
			//settings defaults
			$this->settings_defaults = array(
				'profile_tab_reviews'           => 1,
				'profile_tab_reviews_privacy'   => 0,

				'members_show_rating'           => 1,
				'review_date_format'            => 'j F Y',
				'can_flag_review'               => 'everyone',
				'enable_review_reply'           => 0,

				'review_notice_on'              => 1,
				'review_notice_sub'             => 'You\'ve got a new {rating} review!',
				'review_notice'                 => 'Hi {display_name},<br /><br />' .
						'You\'ve received a new {rating} review from {reviewer}!<br /><br />' .
						'Here is the review content:<br /><br />' .
						'{review_content}<br /><br />' .
						'{reviews_link}<br /><br />' .
						'This is an automated notification from {site_name}. You do not need to reply.',

				'review_reply_notice_on'        => 1,
				'review_reply_notice_sub'       => 'You\'ve got a new reply!',
				'review_reply_notice'           => 'Hi {reviewer},<br /><br />' .
						'You\'ve received a new reply from {user}!<br /><br />' .
						'Here is the reply content:<br /><br />' .
						'{reply_content}<br /><br />' .
						'Here is the review content:<br /><br />' .
						'{review_content}<br /><br />' .
						'{reviews_link}<br /><br />' .
						'This is an automated notification from {site_name}. You do not need to reply.'
			);


			$notification_types['user_review'] = array(
					'title' => __('New user review','um-reviews'),
					'template' => __('<strong>{member}</strong> has left you a new review. <span class="b1">"{review_excerpt}"</span>','um-reviews'),
					'account_desc' => __('When someone leaves me a review','um-reviews'),
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


	/**
	 * Set empty reviews rating
	 */
	function reviews_setup() {

		$users = get_users( array( 'fields' => 'ID','meta_key' => '_reviews_avg', 'meta_compare'  =>  '!=', 'meta_value' => '0') );
		foreach ( $users as $user_id ) {
			$avg_review = get_user_meta( $user_id, '_reviews_avg', true );
			if ( ! $avg_review ) {
				update_user_meta( $user_id, '_reviews_avg', 0.00 );
				update_user_meta( $user_id, '_reviews_total', 0.00 );
			}
		}

	}


	function run_setup() {
		$this->set_default_settings();
		$this->reviews_setup();
	}

}