<?php
namespace um_ext\um_reviews\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Reviews_Member_Directory
 *
 * @package um_ext\um_reviews\core
 */
class Reviews_Member_Directory {


	/**
	 * Reviews_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );
		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'directory_options_profile' ), 11, 1 );

		//for grid
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'reviews_tmpl' ), 1, 1 );

		//for list
		add_action( 'um_members_list_after_user_name_tmpl', array( &$this, 'reviews_tmpl' ), 1, 1 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'extend_ajax_members_data' ), 50, 2 );


		add_filter( 'um_members_directory_filter_types', array( &$this, 'um_reviews_directory_filter_types' ), 10, 1 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'um_reviews_members_directory_sort_dropdown_options' ), 10, 1 );
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'um_reviews_members_directory_filter_fields' ), 10, 1 );

		add_filter( 'um_member_directory_filter_filter_rating_slider', array( &$this, 'um_reviews_directory_filter_rating_slider_range' ), 10, 1 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'filter_rating_slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'sortby_reviews' ), 100, 2 );

		add_filter( 'um_query_args_filter_rating__filter',  array( $this, 'reviews_filter_query' ), 10, 4 );
	}


	/**
	 * Enqueue scripts
	 *
	 */
	function directory_enqueue_scripts() {
		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );
	}


	function directory_options_profile( $fields ) {
		$fields = array_merge( array_slice( $fields, 0, 3 ), array(
			array(
				'id'    => '_um_members_hide_rating',
				'type'  => 'checkbox',
				'label' => __( 'Hide rating stars', 'um-reviews' ),
				'value' => UM()->query()->get_meta_value( '_um_members_hide_rating', null, 'na' ),
			),
		), array_slice( $fields, 3, count( $fields ) - 1 ) );

		return $fields;
	}


	/**
	 * Add user rating in members directory
	 *
	 * @param $args
	 */
	function reviews_tmpl( $args ) {
		$hide_rating = ! empty( $args['members_hide_rating'] ) ? $args['members_hide_rating'] : ! UM()->options()->get( 'members_show_rating' );

		if ( empty( $hide_rating ) ) { ?>
			<# if ( typeof user.user_rating_html !== 'undefined' ) { #>
				{{{user.user_rating_html}}}
			<# } #>
		<?php }
	}


	/**
	 * Extends AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function extend_ajax_members_data( $data_array, $user_id ) {
		ob_start();

		UM()->get_template( 'member-rating.php', um_reviews_plugin, array( 'user_id' => $user_id ), true );

		$data_array['user_rating_html'] = ob_get_clean();
		return $data_array;
	}


	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function um_reviews_directory_filter_types( $filters ) {
		$filters['filter_rating'] = 'slider';

		return $filters;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_reviews_members_directory_sort_dropdown_options( $options ) {
		$options['top_rated'] = __( 'Highest rated first', 'um-reviews' );
		$options['least_rated'] = __( 'Lowest rated first', 'um-reviews' );

		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_reviews_members_directory_filter_fields( $options ) {
		$options['filter_rating'] = __( 'User rating', 'um-reviews' );

		return $options;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_reviews_directory_filter_rating_slider_range( $range ) {
		global $wpdb;

		$meta = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->usermeta} WHERE meta_key='_reviews_avg' ORDER BY meta_value DESC" );

		if ( empty( $meta ) ) {
			$range = false;
		} else {
			$range = array( 0, max( $meta ) );
		}

		return $range;
	}


	function filter_rating_slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'filter_rating' ) {
			return '<strong>' . __( 'Rating', 'um-reviews' ) . ':</strong>&nbsp;{min_range} - {max_range}' . __( ' stars', 'um-reviews' );
		}

		return $placeholder;
	}




	/**
	 * Adding default order on directory
	 *
	 * @param array $query_args
	 * @param $sortby
	 *
	 * @return array
	 */
	function sortby_reviews( $query_args, $sortby ) {
		if ( $sortby != 'top_rated' && $sortby != 'least_rated' ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['meta_query'][] = array(
			'relation'      => 'OR',
			array(
				'key'       => '_reviews_avg',
				'compare'   => 'EXISTS',
				'type'      => 'NUMERIC',
			),
			'_reviews' => array(
				'key'       => '_reviews_avg',
				'compare'   => 'NOT EXISTS',
				'type'      => 'NUMERIC',
			)
		);

		if ( $sortby == 'top_rated' ) {

			$query_args['orderby'] = array( '_reviews' => 'DESC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		} elseif ( $sortby == 'least_rated' ) {

			$query_args['orderby'] = array( '_reviews' => 'ASC', 'user_registered' => 'DESC' );
			unset( $query_args['order'] );

		}

		return $query_args;
	}


	/**
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return mixed
	 */
	function reviews_filter_query( $query, $field, $value, $filter_type ) {

		$query = array(
			'key'       => '_reviews_avg',
			'value'     => array_map( 'absint', $value ),
			'compare'   => 'BETWEEN',
			'type'      => 'NUMERIC',
			'inclusive' => true,
		);

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return $query;
	}

}