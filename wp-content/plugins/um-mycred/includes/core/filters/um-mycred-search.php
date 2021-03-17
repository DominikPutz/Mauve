<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param $query_args
 * @param $sortby
 *
 * @return mixed
 */
function um_mycred_sortby_points( $query_args, $sortby ) {

	if ( $sortby == 'most_mycred_points' || $sortby == 'least_mycred_points' ) {
		$query_args['orderby']  = 'meta_value_num,user_registered';
		$query_args['order']    = $sortby == 'most_mycred_points' ? 'asc' : 'desc';

		$query_args['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'=>'mycred_default',
				'compare' => 'EXISTS'
			),
			array(
				'key'=>'mycred_default',
				'compare' => 'NOT EXISTS'
			)
		);

	}
	
	return $query_args;
}
add_filter( 'um_modify_sortby_parameter', 'um_mycred_sortby_points', 100, 2 );