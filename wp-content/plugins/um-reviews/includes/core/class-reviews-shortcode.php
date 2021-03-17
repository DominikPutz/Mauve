<?php
namespace um_ext\um_reviews\core;

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class Reviews_Shortcode {

	/**
	 * Reviews_Shortcode constructor.
	 */
	function __construct() {

		add_shortcode( 'ultimatemember_top_rated', array( &$this, 'ultimatemember_top_rated' ) );
		add_shortcode( 'ultimatemember_most_rated', array( &$this, 'ultimatemember_most_rated' ) );
		add_shortcode( 'ultimatemember_lowest_rated', array( &$this, 'ultimatemember_lowest_rated' ) );
	}


	/**
	 * Most Rated Shortcode
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_most_rated( $args = array() ) {

		$defaults = array(
			'roles'	 => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'fields'	 => 'ID',
			'number'	 => $args[ 'number' ],
			'meta_key' => '_reviews_total',
			'orderby'	 => 'meta_value',
			'order'		 => 'desc'
		);

		if ( $args[ 'roles' ] && $args[ 'roles' ] != 'all' ) {
			$query_args[ 'role__in' ] = $args[ 'roles' ];
		}

		$users = new \WP_User_Query( $query_args );
		if ( empty( $users ) ) {
			return '';
		}

		$args_t = array_merge( $args, array(
			'list_class' => 'most-rated',
			'users'			 => apply_filters( 'um-reviews-widget-most-rated-users', $users, $args ),
			) );
		$output = UM()->get_template( 'reviews-widget.php', um_reviews_plugin, $args_t );

		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		return $output;
	}


	/**
	 * Top Rated Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_top_rated( $args = array() ) {

		$defaults = array(
			'roles'	 => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'fields'		 => 'ID',
			'number'		 => $args[ 'number' ],
			'meta_query' => array(
				'_reviews_avg'	 => array(
					'key'			 => '_reviews_avg',
					'compare'	 => 'EXISTS'
				),
				'_reviews_total' => array(
					'key'			 => '_reviews_total',
					'compare'	 => 'EXISTS'
				),
				'relation'			 => 'AND',
			),
			'orderby'		 => array(
				'_reviews_avg'	 => 'desc',
				'_reviews_total' => 'desc'
			)
		);

		if ( $args[ 'roles' ] && $args[ 'roles' ] != 'all' ) {
			$query_args[ 'role__in' ] = $args[ 'roles' ];
		}

		$users = new \WP_User_Query( $query_args );
		if ( empty( $users ) ) {
			return '';
		}

		$args_t = array_merge( $args, array(
			'list_class' => 'top-rated',
			'users'			 => apply_filters( 'um-reviews-widget-top-rated-users', $users, $args ),
			) );
		$output = UM()->get_template( 'reviews-widget.php', um_reviews_plugin, $args_t );

		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		return $output;
	}


	/**
	 * Lowest Rated Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_lowest_rated( $args = array() ) {

		$defaults = array(
			'roles'	 => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'fields'	 => 'ID',
			'number'	 => $args[ 'number' ],
			'meta_key' => '_reviews_avg',
			'orderby'	 => 'meta_value',
			'order'		 => 'asc'
		);

		if ( $args[ 'roles' ] && $args[ 'roles' ] != 'all' ) {
			$query_args[ 'role__in' ] = $args[ 'roles' ];
		}

		$users = new \WP_User_Query( $query_args );
		if ( empty( $users ) ) {
			return '';
		}

		$args_t = array_merge( $args, array(
			'list_class' => 'lowest-rated',
			'users'			 => apply_filters( 'um-reviews-widget-lowest-rated-users', $users, $args ),
			) );
		$output = UM()->get_template( 'reviews-widget.php', um_reviews_plugin, $args_t );

		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		return $output;
	}

	//class end
}
