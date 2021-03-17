<?php
namespace um_ext\um_followers\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Member_Directory
 *
 * @package um_ext\um_followers\core
 */
class Followers_Member_Directory {


	/**
	 * Followers_Member_Directory constructor.
	 */
	function __construct() {
		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'um_followers_admin_directory_options_profile' ), 11, 1 );
		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_followers_sort_user_option' ), 10, 1 );
		add_action( 'um_pre_directory_shortcode', array( &$this, 'um_followers_directory_enqueue_scripts' ), 10, 1 );

		// for grid
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'followers_stats' ), 1, 1 );
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'followers_button' ), 101, 1 );

		//for list
		add_action( 'um_members_list_after_user_name_tmpl', array( &$this, 'followers_stats' ), 1, 1 );
		add_action( 'um_members_list_just_after_actions_tmpl', array( &$this, 'followers_button' ), 101, 1 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'um_followers_ajax_get_members_data' ), 50, 2 );


		add_filter( 'um_modify_sortby_parameter', array( &$this, 'um_followers_sortby_followed' ), 100, 2 );
		add_filter( 'pre_user_query', array( &$this, 'um_wp_user_filter_by_followers' ), 100 );
	}


	/**
	 * Admin options for directory filtering
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function um_followers_admin_directory_options_profile( $fields ) {
		$fields = array_merge( array_slice( $fields, 0, 3 ), array(
			array(
				'id'    => '_um_followers_hide_stats',
				'type'  => 'checkbox',
				'label' => __( 'Hide followers stats', 'um-followers' ),
				'value' => UM()->query()->get_meta_value( '_um_followers_hide_stats', null, 'na' ),
			),
			array(
				'id'    => '_um_followers_hide_button',
				'type'  => 'checkbox',
				'label' => __( 'Hide follow button', 'um-followers' ),
				'value' => UM()->query()->get_meta_value( '_um_followers_hide_button', null, 'na' ),
			),
		), array_slice( $fields, 3, count( $fields ) - 1 ) );

		return $fields;
	}


	/**
	 * Sort by Followers
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_followers_sort_user_option( $options ) {
		$options['most_followed'] = __( 'Most followed', 'um-followers' );
		$options['least_followed'] = __( 'Least followed', 'um-followers' );
		return $options;
	}


	/**
	 * Enqueue scripts on member directory
	 *
	 * @param $args
	 */
	function um_followers_directory_enqueue_scripts( $args ) {
		$global_followers_show_stats = UM()->options()->get( 'followers_show_stats' );
		$global_followers_show_button = UM()->options()->get( 'followers_show_button' );

		if ( ( empty( $args['followers_hide_stats'] ) && ! empty( $global_followers_show_stats ) ) ||
		     ( empty( $args['followers_hide_button'] ) && ! empty( $global_followers_show_button ) ) ) {
			wp_enqueue_style( 'um_followers' );
			wp_enqueue_script( 'um_followers' );
		}
	}


	/**
	 * Add button to member directory
	 *
	 * @param $args
	 */
	function followers_button( $args ) {
		$hide_followers_button = ! empty( $args['followers_hide_button'] ) ? $args['followers_hide_button'] : ! UM()->options()->get( 'followers_show_button' );

		if ( empty( $hide_followers_button ) ) { ?>
			<# if ( user.followers_button ) { #>
				<div class="um-members-follow-btn um-members-list-footer-button-wrapper">{{{user.followers_button}}}</div>
			<# } #>
		<?php }
	}


	/**
	 * Add stats to member directory
	 *
	 * @param $args
	 */
	function followers_stats( $args ) {
		$hide_followers_button = ! empty( $args['followers_hide_stats'] ) ? $args['followers_hide_stats'] : ! UM()->options()->get( 'followers_show_stats' );

		if ( empty( $hide_followers_button ) ) { ?>
			<# if ( user.followers_show_stats ) { #>
				<div class="um-members-follow-stats">
					<div>{{{user.followers_count}}} <?php _e( 'followers', 'um-followers' ); ?></div>
					<div>{{{user.following_count}}} <?php _e( 'following', 'um-followers' ); ?></div>
				</div>
			<# } #>
		<?php }
	}


	/**
	 * Expand AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function um_followers_ajax_get_members_data( $data_array, $user_id ) {
		$data_array['followers_button'] = UM()->Followers_API()->api()->follow_button( $user_id, get_current_user_id() );

		$data_array['followers_show_stats'] = true;
		if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Followers', 'um-followers' ) );
			$is_private_case = UM()->user()->is_private_case( $user_id, 'follower' );
			if ( $is_private_case || $is_private_case_old ) { // only followers can view my profile
				$data_array['followers_show_stats'] = false;
			}

			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Only people I follow can view my profile', 'um-followers' ) );
			$is_private_case = UM()->user()->is_private_case( $user_id, 'followed' );
			if ( $is_private_case || $is_private_case_old ) { // only people i follow can view my profile
				$data_array['followers_show_stats'] = false;
			}
		}

		if ( $data_array['followers_show_stats'] ) {
			$data_array['followers_count'] = UM()->Followers_API()->api()->count_followers( $user_id );
			$data_array['following_count'] = UM()->Followers_API()->api()->count_following( $user_id );
		}

		return $data_array;
	}


	/**
	 * Adding sort directories by followers
	 *
	 * @param $query_args
	 * @param $sortby
	 *
	 * @return mixed
	 */
	function um_followers_sortby_followed( $query_args, $sortby ) {
		if ( $sortby != 'most_followed' && $sortby != 'least_followed' ) {
			return $query_args;
		}

		$query_args['orderby'] = 'followers';
		$query_args['order'] = $sortby == 'most_followed' ? 'DESC' : 'ASC';

		return $query_args;
	}


	/**
	 * Adding sort directories by followers
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	function um_wp_user_filter_by_followers( $query ) {
		global $wpdb;

		if ( isset( $query->query_vars['orderby'] ) && 'followers' == $query->query_vars['orderby'] ) {
			$followers_table = UM()->Followers_API()->api()->table_name;

			$order = isset( $query->query_vars['order'] ) ? $query->query_vars['order'] : 'DESC';
			$query->query_orderby = sprintf( "ORDER BY ( SELECT COUNT(*) FROM {$followers_table} WHERE {$wpdb->users}.ID = {$followers_table}.user_id1 ) %s", $order );
		}

		return $query;
	}


}