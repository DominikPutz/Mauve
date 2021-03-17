<?php
namespace um_ext\um_woocommerce\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Member_Directory
 *
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Member_Directory {


	/**
	 * WooCommerce_Member_Directory constructor.
	 */
	function __construct() {
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_woo_order_count_slider', array( &$this, 'um_woocommerce_directory_filter_woo_order_count_slider' ), 10, 1 );
		add_filter( 'um_member_directory_filter_woo_total_spent_slider', array( &$this, 'um_woocommerce_directory_filter_woo_total_spent_slider' ), 10, 1 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_search_fields',  array( $this, 'country_dropdown' ), 10, 1 );

		add_filter( 'um_query_args_woo_order_count__filter', array( $this, 'filter_by_orders_count' ), 10, 4 );
		add_filter( 'um_query_args_woo_total_spent__filter', array( $this, 'filter_by_total_spent' ), 10, 4 );

		add_filter( 'um_search_fields', array( &$this, 'change_filter_label' ), 10, 2 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'get_members_data' ), 50, 2 );
	}


	function filter_by_orders_count( $query, $field, $value, $filter_type ) {
		global $wpdb;

		$user_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT pm.meta_value AS user_id
			FROM $wpdb->posts as p 
			LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
			WHERE pm.meta_key = '_customer_user' AND     
				  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
				  p.post_status IN ('" . implode( "','", array('wc-completed') )  . "')
			GROUP BY pm.meta_value
			HAVING COUNT( p.ID ) BETWEEN %d AND %d",
			$value[0],
			$value[1]
		) );

		if ( ! empty( $user_ids ) ) {
			UM()->member_directory()->query_args['include'] = $user_ids;
		} else {
			UM()->member_directory()->query_args['include'] = array('0');
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return true;
	}


	function filter_by_total_spent( $query, $field, $value, $filter_type ) {
		global $wpdb;

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$user_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT meta.meta_value
			FROM {$wpdb->posts} as posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
			WHERE meta.meta_key       = '_customer_user' AND
			      posts.post_type     = 'shop_order' AND
			      posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND
			                          meta2.meta_key      = '_order_total'
			GROUP BY meta.meta_value
			HAVING SUM( meta2.meta_value ) BETWEEN %d AND %d",
			$value[0],
			$value[1]
		) );

		if ( ! empty( $user_ids ) ) {
			UM()->member_directory()->query_args['include'] = $user_ids;
		} else {
			UM()->member_directory()->query_args['include'] = array('0');
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return true;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function members_directory_filter_fields( $options ) {
		$options['billing_country'] = __( 'WC Billing country', 'um-woocommerce' );
		$options['shipping_country'] = __( 'WC Shipping country', 'um-woocommerce' );
		$options['woo_order_count'] = __( 'Total Orders', 'um-woocommerce' );
		$options['woo_total_spent'] = __( 'Total Spent', 'um-woocommerce' );

		return $options;
	}


	function directory_filter_types( $filters ) {
		$filters['billing_country'] = 'select';
		$filters['shipping_country'] = 'select';
		$filters['woo_order_count'] = 'slider';
		$filters['woo_total_spent'] = 'slider';

		return $filters;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_woocommerce_directory_filter_woo_order_count_slider( $range ) {
		global $wpdb;

		$counts = $wpdb->get_col(
			"SELECT COUNT( p.ID ) as woo_orders
			FROM $wpdb->posts as p 
			LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
			WHERE pm.meta_key = '_customer_user' AND     
				  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
				  p.post_status IN ('" . implode( "','", array('wc-completed') )  . "')
			GROUP BY pm.meta_value
			ORDER BY woo_orders"
		);

		if ( empty( $counts ) ) {
			$range = false;
		} else {
			$range = array( 0, max( $counts ) );
		}

		return $range;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_woocommerce_directory_filter_woo_total_spent_slider( $range ) {
		global $wpdb;

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
		$meta = $wpdb->get_col(
			"SELECT SUM( meta2.meta_value )
			FROM $wpdb->posts as posts
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
			WHERE meta.meta_key       = '_customer_user' AND
			      posts.post_type     = 'shop_order' AND
			      posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND
			      meta2.meta_key      = '_order_total'
			GROUP BY meta.meta_value"
		);

		if ( empty( $meta ) ) {
			$range = false;
		} elseif ( ! empty( $meta ) ) {
			$range = array( 0, max( $meta ) );
		}

		return $range;
	}


	function slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'woo_order_count' ) {
			$placeholder = __( '<strong>Total Orders (' . get_woocommerce_currency_symbol() . '):</strong>&nbsp;{min_range} - {max_range}', 'um-woocommerce' );
		} elseif ( $filter == 'woo_total_spent' ) {
			$placeholder = __( '<strong>Total Spent (' . get_woocommerce_currency_symbol() . '):</strong>&nbsp;{min_range} - {max_range}', 'um-woocommerce' );
		}

		return $placeholder;
	}


	function country_dropdown( $attrs ) {
		if ( isset( $attrs['metakey'] ) && ( 'billing_country' == $attrs['metakey'] || 'shipping_country' == $attrs['metakey'] ) ) {
			$countries_obj   = new \WC_Countries();
			$countries   = $countries_obj->__get('countries');

			$attrs['options'] = $countries;
			$attrs['custom'] = true;
		}

		return $attrs;
	}


	/**
	 * Remove "WC " from Woo address fields labels
	 *
	 * @param array $attrs
	 *
	 * @return array
	 */
	function change_filter_label( $attrs, $field_key ) {
		$address_field_keys = UM()->WooCommerce_API()->api()->get_wc_address_fields( true );

		if ( in_array( $field_key, $address_field_keys ) ) {
			$attrs['label'] = substr( $attrs['label'], 3 );
		}
		return $attrs;
	}


	/**
	 * Expand AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function get_members_data( $data_array, $user_id ) {
		if ( isset( $data_array['billing_country'] ) || isset( $data_array['shipping_country'] ) ) {
			$countries = UM()->builtin()->get( 'countries' );

			if ( isset( $data_array['billing_country'] ) && strlen( $data_array['billing_country'] ) == 2 ) {
				$lang_code = $data_array['billing_country'];
				$data_array['billing_country'] = $countries[ $lang_code ];
			}
			if ( isset( $data_array['shipping_country'] ) && strlen( $data_array['shipping_country'] ) == 2 ) {
				$lang_code = $data_array['shipping_country'];
				$data_array['shipping_country'] = $countries[ $lang_code ];
			}
		}
		return $data_array;
	}
}