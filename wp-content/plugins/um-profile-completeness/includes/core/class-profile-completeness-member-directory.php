<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Member_Directory
 *
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Member_Directory {


	/**
	 * Profile_Completeness_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_admin_extend_directory_options_general', array( &$this, 'um_profile_completeness_admin_directory' ), 10, 1 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_profile_completeness_members_directory_sort_dropdown_options' ), 10, 1 );
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'um_profile_completeness_members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'um_profile_completeness_directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_completeness_bar_slider', array( &$this, 'um_profile_completeness_directory_filter_completeness_bar' ), 10, 2 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'filter_completeness_bar_slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_prepare_user_query_args', array( &$this, 'completed_add_search_to_query' ), 40, 2 );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'sortby_completeness' ), 100, 2 );

		add_filter( 'um_query_args_completeness_bar__filter',  array( $this, 'completeness_filter_query' ), 10, 4 );
	}


	/**
	 * Enqueue scripts
	 *
	 */
	function directory_enqueue_scripts() {
		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );
	}


	/**
	 * Admin options for directory filtering
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function um_profile_completeness_admin_directory( $fields ) {
		$additional_fields = array(
			array(
				'id'    => '_um_has_completed_profile',
				'type'  => 'checkbox',
				'label' => __( 'Only show members who have completed their profile', 'um-profile-completeness' ),
				'value' => UM()->query()->get_meta_value( '_um_has_completed_profile', null, 'na' ),
			),
			array(
				'id'            => '_um_has_completed_profile_pct',
				'type'          => 'text',
				'label'         => __( 'Required completeness (%)', 'um-profile-completeness' ),
				'value'         => UM()->query()->get_meta_value('_um_has_completed_profile_pct', null, 'na' ),
				'conditional'   => array( '_um_has_completed_profile', '=', '1' ),
				'size'          => 'small'
			)
		);

		return array_merge( $fields, $additional_fields );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_profile_completeness_members_directory_sort_dropdown_options( $options ) {
		$options['most_completed'] = __( 'Most completed', 'um-profile-completeness' );
		$options['least_completed'] = __( 'Least completed', 'um-profile-completeness' );

		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_profile_completeness_members_directory_filter_fields( $options ) {
		$options['completeness_bar'] = __( 'Profile Completeness', 'um-profile-completeness' );

		return $options;
	}


	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function um_profile_completeness_directory_filter_types( $filters ) {
		$filters['completeness_bar'] = 'slider';

		return $filters;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_profile_completeness_directory_filter_completeness_bar( $range, $directory_data ) {
		global $wpdb;

		$meta = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->usermeta} WHERE meta_key='_completed' ORDER BY meta_value DESC" );

		if ( empty( $meta ) ) {
			$range = false;
		} else {
			if ( ! empty( $directory_data['has_completed_profile'] ) && ! empty( $directory_data['has_completed_profile_pct'] ) ) {
				$range = array( absint( $directory_data['has_completed_profile_pct'] ), max( $meta ) );
			} else {
				$range = array( 0, max( $meta ) );
			}
		}

		return $range;
	}


	function filter_completeness_bar_slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'completeness_bar' ) {
			return '<strong>' . __( 'Profile Completed', 'um-profile-completeness' ) . ':</strong>&nbsp;{min_range} - {max_range}%';
		}

		return $placeholder;
	}


	function completed_add_search_to_query( $query_args, $directory_data ) {
		if ( ! empty( $directory_data['has_completed_profile'] ) && ! empty( $directory_data['has_completed_profile_pct'] ) ) {
			if ( empty( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$completed = absint( $directory_data['has_completed_profile_pct'] ) > 100 ? 100 : absint( $directory_data['has_completed_profile_pct'] );
			$query_args['meta_query'][] = array(
				'key'       => '_completed',
				'value'     => $completed,
				'compare'   => '>=',
				'type'      =>'NUMERIC'
			);
		}

		return $query_args;
	}


	function sortby_completeness( $query_args, $sortby ) {
		if ( $sortby != 'most_completed' && $sortby != 'least_completed' ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['meta_query'][] = array(
			'relation'      => 'OR',
			array(
				'key'       => '_completed',
				'compare'   => 'EXISTS',
				'type'      => 'NUMERIC',
			),
			'no_complete' => array(
				'key'       => '_completed',
				'compare'   => 'NOT EXISTS',
				'type'      => 'NUMERIC',
			),
		);

		if ( $sortby == 'most_completed' ) {

			$query_args['orderby'] = array( 'no_complete' => 'DESC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		} elseif ( $sortby == 'least_completed' ) {

			$query_args['orderby'] = array( 'no_complete' => 'ASC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		}

		return $query_args;
	}


	function completeness_filter_query( $query, $field, $value, $filter_type ) {
		$query = array(
			'key'       => '_completed',
			'value'     => array_map( 'absint', $value ),
			'compare'   => 'BETWEEN',
			'type'      => 'NUMERIC',
			'inclusive' => true,
		);

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return $query;
	}
}