<?php
namespace um_ext\um_social_activity\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Activity_Setup
 * @package um_ext\um_social_activity\core
 */
class Activity_Setup {
	var $settings_defaults;

	var $global_actions;

	function __construct() {

		$this->global_actions['status'] = __('New wall post','um-activity');
		$this->global_actions['new-user'] = __('New user','um-activity');
		$this->global_actions['new-post'] = __('New blog post','um-activity');
		$this->global_actions['new-product'] = __('New product','um-activity');
		$this->global_actions['new-gform'] = __('New Gravity Form','um-activity');
		$this->global_actions['new-gform-submission'] = __('New Gravity Form Answer','um-activity');
		$this->global_actions['new-follow'] = __('New follow','um-activity');
		$this->global_actions['new-topic'] = __('New forum topic','um-activity');

		//settings defaults
		$this->settings_defaults = array(
			'activity_posts_num' => 10,
			'activity_max_faces' => 10,
			'activity_posts_num_mob' => 5,
			'activity_init_comments_count' => 2,
			'activity_load_comments_count' => 10,
			'activity_order_comment' => 'asc',
			'activity_post_truncate' => 25,
			'activity_enable_privacy' => 1,
			'activity_trending_days' => 7,
			'activity_require_login' => 0,
			'activity_need_to_login' => __( 'Please <a href="{register_page}" class="um-link">sign up</a> or <a href="{login_page}" class="um-link">sign in</a> to like or comment on this post.', 'um-activity' ),
			'activity_followers_mention' => 1,
			'activity_friends_mention' => 1,
			'activity_followed_users' => 0,
			'activity_friends_users' => 0,
			'profile_tab_activity'           => 1,
			'profile_tab_activity_privacy'   => 0,
			'activity_highlight_color'  => '#0085ba'
		);

		foreach ( apply_filters( 'um_activity_global_actions', $this->global_actions ) as $k => $v ) {
			if ( $k == 'status' ) {
				continue;
			}

			$this->settings_defaults['activity-' . $k] = 1;
		}


		$notification_types['new_wall_post'] = array(
			'title'         => __('User get a new wall post','um-activity'),
			'template'      => '<strong>{member}</strong> has posted on your wall.',
			'account_desc'  => __('When someone publish a post on my wall','um-activity'),
		);

		$notification_types['new_wall_comment'] = array(
			'title'         => __('User get a new wall comment','um-activity'),
			'template'      => '<strong>{member}</strong> has commented on your wall post.',
			'account_desc'  => __('When someone comments on your post','um-activity'),
		);

		$notification_types['new_post_like'] = array(
			'title'         => __('User get a new post like','um-activity'),
			'template'      => '<strong>{member}</strong> likes your wall post.',
			'account_desc'  => __('When someone likes your post','um-activity'),
		);

		$notification_types['new_mention'] = array(
			'title'         => __( 'User get a new mention', 'um-activity' ),
			'template'      => '<strong>{member}</strong> just mentioned you.',
			'account_desc'  => __( 'When someone mentions me', 'um-activity' ),
		);

		foreach ( $notification_types as $k => $desc ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $desc['template'];
		}
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


	function run_setup() {
		$this->setup();
		$this->set_default_settings();
	}


	/**
	 * Setup
	 */
	function setup() {
		$version = get_option( 'um_activity_version' );

		if ( ! $version ) {
			$options = get_option( 'um_options', array() );

			//only on first install
			$page_exists = UM()->query()->find_post_id( 'page', '_um_core', 'activity' );
			if ( ! $page_exists ) {

				$user_page = array(
					'post_title'        => __( 'Activity', 'um-activity' ),
					'post_content'      => '[ultimatemember_activity]',
					'post_name'         => 'activity',
					'post_type'         => 'page',
					'post_status'       => 'publish',
					'post_author'       => get_current_user_id(),
					'comment_status'    => 'closed'
				);

				$post_id = wp_insert_post( $user_page );

				if ( $post_id ) {
					update_post_meta( $post_id, '_um_core', 'activity');
				}

			} else {
				$post_id = $page_exists;
			}

			if ( $post_id ) {
				$key = UM()->options()->get_core_page_id( 'activity' );
				$options[ $key ] = $post_id;
			}

			update_option( 'um_options', $options );
		}
	}

}