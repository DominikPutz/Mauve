<?php
namespace um_ext\um_verified_users\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Verified_Users_Member_Directory
 *
 * @package um_ext\um_verified_users\core
 */
class Verified_Users_Member_Directory {


	/**
	 * Verified_Users_Member_Directory constructor.
	 */
	function __construct() {
		add_filter( 'um_admin_extend_directory_options_general', array( &$this, 'um_verified_option_show_only_verified' ), 10, 1 );
		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_verified_sort_user_option' ), 10, 1 );
		add_action( 'um_pre_directory_shortcode', array( &$this, 'um_verified_directory_enqueue_scripts' ), 10, 1 );


		add_filter( 'um_modify_sortby_parameter', array( &$this, 'um_verified_sortby_' ), 100, 2 );
		add_filter( 'um_prepare_user_query_args', array( &$this, 'um_verified_add_search_to_query' ), 40, 2 );

		add_filter( 'um_member_directory_pre_display_sorting', array( &$this, 'sorting_options' ), 10, 2 );
	}


	/**
	 * Remove unverified first sorting option if we display only verified users
	 *
	 * @param array $options
	 * @param array $directory_data
	 *
	 * @return array
	 */
	function sorting_options( $options, $directory_data ) {
		if ( ! empty( $directory_data['show_only_verified'] ) && in_array( 'unverified_first', array_keys( $options ) ) ) {
			unset( $options['unverified_first'] );
		}

		return $options;
	}


	/**
	 * Member Directory option "Only show members who are verified"
	 *
	 * @hooked	'um_admin_extend_directory_options_general'
	 * @since		2.0.5
	 *
	 * @param		array		$fields
	 * @return	array
	 */
	function um_verified_option_show_only_verified( $fields ) {
		if( is_array( $fields ) ) {

			$fields[] = array(
				'id'    => '_um_show_only_verified',
				'type'  => 'checkbox',
				'label' => __( 'Only show members who have verified their profile', 'um-verified' ),
				'value' => UM()->query()->get_meta_value( '_um_show_only_verified', null, 'na' ),
			);
		}

		return $fields;
	}


	/**
	 * Sort by verified accounts
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_verified_sort_user_option( $options ) {
		$options['verified_first'] = __( 'Verified accounts first', 'um-verified' );
		$options['unverified_first'] = __( 'Not verified accounts first', 'um-verified' );
		return $options;
	}


	/**
	 * Enqueue styles
	 */
	function um_verified_directory_enqueue_scripts() {
		wp_enqueue_style( 'um-verified' );
	}


	/**
	 * Adding default order on directory
	 *
	 * @param $query_args
	 * @param $sortby
	 *
	 * @return mixed
	 */
	function um_verified_sortby_( $query_args, $sortby ) {

		if ( $sortby == 'verified_first' ||  $sortby == 'unverified_first' ) {
			if ( empty( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'relation'      => 'OR',
				'verified'      => array(
					'key'   => '_um_verified',
					'value' => 'verified'
				),
				'unverified'    => array(
					'key'   => '_um_verified',
					'value' => 'unverified'
				)
			);

			if ( $sortby == 'verified_first' ) {

				$query_args['orderby'] = array( 'verified' => 'DESC' );
				$query_args['order'] = 'DESC';

			} elseif ( $sortby == 'unverified_first' ) {

				$query_args['orderby'] = array( 'verified' => 'ASC' );
				$query_args['order'] = 'ASC';

			}
		}

		return $query_args;
	}


	/**
	 * Member Directory filter "Only show members who are verified"
	 *
	 * @hooked 'um_prepare_user_query_args'
	 * @since 2.0.5
	 *
	 * @param array $query_args
	 * @param array $directory_data
	 * @return array
	 */
	function um_verified_add_search_to_query( $query_args, $directory_data ) {

		if ( ! empty( $directory_data['show_only_verified'] ) ) {
			if ( empty( $query_args['meta_query'] ) ) {
				$query_args['meta_query'] = array();
			}

			$query_args['meta_query'][] = array(
				'key'       => '_um_verified',
				'value'     => 'verified',
				'compare'   => '='
			);
		}

		return $query_args;
	}



}