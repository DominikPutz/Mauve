<?php
namespace um_ext\um_user_tags\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Member_Directory
 *
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Member_Directory {


	/**
	 * User_Tags_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_members_directory_custom_field_types_supported_filter', array( &$this, 'custom_field_types_supported_filter' ), 10, 1 );
		add_filter( 'um_search_fields', array( &$this, 'user_tags_filter_dropdown' ), 10, 1 );

		add_filter( 'um_member_directory_general_search_meta_query', array( &$this, 'extends_search_query' ), 10, 2 );
	}


	/**
	 * Enqueue scripts on member directory
	 *
	 * @param $args
	 */
	function directory_enqueue_scripts( $args ) {
		wp_enqueue_style( 'um-user-tags' );
		wp_enqueue_script( 'um-user-tags' );
		wp_enqueue_script( 'um-user-tags-members' );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function custom_field_types_supported_filter( $options ) {
		$options[] = 'user_tags';
		return $options;
	}



	/**
	 * @param $attrs
	 * @return bool
	 */
	function user_tags_filter_dropdown( $attrs ) {
		if ( isset( $attrs['type'] ) && 'user_tags' == $attrs['type'] ) {
			$attrs['options'] = apply_filters( 'um_multiselect_options_user_tags', array(), $attrs );
			$attrs['custom']  = 1;
		}

		return $attrs;
	}


	/**
	 * @param $query
	 * @param $search
	 *
	 * @return array
	 */
	function extends_search_query( $query, $search ) {

		$term = get_term_by( 'name', trim( $search ), 'um_user_tag' );

		if ( ! empty( $term->term_id ) ) {
			$query = array_merge( $query, array(
				array(
					'value'     => serialize( strval( $term->term_id ) ),
					'compare'   => 'LIKE',
				),
				array(
					'value'     => serialize( strval( $term->slug ) ),
					'compare'   => 'LIKE',
				),
				'relation' => 'OR',
			) );
		}

		return $query;
	}

}