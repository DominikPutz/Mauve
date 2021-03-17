<?php
namespace um_ext\um_mycred\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class myCRED_Member_Directory
 *
 * @package um_ext\um_mycred\core
 */
class myCRED_Member_Directory {


	/**
	 * myCRED_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'sort_dropdown_options' ), 10, 1 );
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'um_mycred_members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'um_mycred_directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'mycred_default_slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_search_fields',  array( $this, 'mycred_rank_dropdown' ), 10, 1 );
		add_filter( 'um_query_args_mycred_default__filter',  array( $this, 'mycred_default_filter_query' ), 10, 4 );

		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'admin_directory_options_profile' ), 11, 1 );

		//for grid
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'badges_tmpl' ), 1, 1 );

		//for list
		add_action( 'um_members_list_after_user_name_tmpl', array( &$this, 'badges_tmpl' ), 1, 1 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'extend_ajax_members_data' ), 50, 2 );

		add_filter( 'um_search_fields', array( &$this, 'change_filter_label' ), 10, 2 );
	}


	/**
	 * Enqueue scripts
	 *
	 */
	function directory_enqueue_scripts() {
		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function sort_dropdown_options( $options ) {
		$options['most_mycred_points'] = __( 'Most points', 'um-mycred' );
		$options['least_mycred_points'] = __( 'Least points', 'um-mycred' );

		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_mycred_members_directory_filter_fields( $options ) {
		if ( function_exists( 'mycred_have_ranks' ) ) {
			$options['mycred_rank'] = __( 'myCRED Rank', 'um-mycred' );
		}
		$options['mycred_default'] = __( 'myCRED Balance', 'um-mycred' );

		return $options;
	}


	/**
	 * @param array $attrs
	 *
	 * @return array
	 */
	function change_filter_label( $attrs, $field_key ) {
		if ( $field_key === 'mycred_rank' ) {
			$attrs['label'] = __( 'Rank', 'um-mycred' );
		}
		return $attrs;
	}



	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function um_mycred_directory_filter_types( $filters ) {
		$filters['mycred_default'] = 'slider';
		if ( function_exists( 'mycred_have_ranks' ) ) {
			$filters['mycred_rank'] = 'select';
		}

		return $filters;
	}


	/**
	 * @param string $placeholder
	 * @param string $filter
	 *
	 * @return string
	 */
	function mycred_default_slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'mycred_default' ) {
			return '<strong>' . __( 'Balance', 'um-mycred' ) . ':</strong>&nbsp;{min_range} - {max_range} ' . __( 'points', 'um-mycred' );
		}

		return $placeholder;
	}


	/**
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function mycred_rank_dropdown( $attrs ) {
		if ( isset( $attrs['metakey'] ) && 'mycred_rank' == $attrs['metakey'] ) {
			$all_ranks = mycred_get_ranks( 'publish', '-1' );

			$options = array();
			if ( ! empty( $all_ranks ) ) {
				foreach ( $all_ranks as $rank ) {
					$options[ $rank->post_id ] = $rank->post->post_title;
				}
			}

			$attrs['options'] = $options;
			$attrs['custom'] = true;
		}

		return $attrs;
	}


	function mycred_default_filter_query( $query, $field, $value, $filter_type ) {
		$query = array(
			'key'       => 'mycred_default',
			'value'     => array_map( 'absint', $value ),
			'compare'   => 'BETWEEN',
			'type'      => 'NUMERIC',
			'inclusive' => true,
		);

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return $query;
	}


	/**
	 * Admin options for directory filtering
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function admin_directory_options_profile( $fields ) {

		if ( function_exists( 'mycred_get_users_badges' ) ) {

			$fields = array_merge( array_slice( $fields, 0, 3 ), array(
				array(
					'id'    => '_um_mycred_hide_badges',
					'type'  => 'checkbox',
					'label' => __( 'Hide myCRED badges', 'um-mycred' ),
					'value' => UM()->query()->get_meta_value( '_um_mycred_hide_badges', null, 'na' ),
				),
			), array_slice( $fields, 3, count( $fields ) - 1 ) );

		}

		return $fields;
	}


	/**
	 * Display badges in Member Directories
	 *
	 * @param $args
	 */
	function badges_tmpl( $args ) {
		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return;
		}

		$hide_badges = ! empty( $args['mycred_hide_badges'] ) ? $args['mycred_hide_badges'] : ! UM()->options()->get( 'mycred_show_badges_in_members' );
		if ( empty( $hide_badges ) ) { ?>
			<# if ( typeof user.badges !== 'undefined' && user.badges !== '' ) { #>
				<div class="um-header" style="border:none;margin:initial;padding:initial;min-height:initial;">{{{user.badges}}}</div>
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
		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return $data_array;
		}

		$data_array['badges'] = UM()->myCRED()->show_badges( $user_id );
		return $data_array;
	}

}