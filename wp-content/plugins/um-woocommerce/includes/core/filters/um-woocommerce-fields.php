<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return array
 */
function um_woocommerce_add_field( $fields ) {

	$fields['woo_total_spent'] = array(
		'title'             => __( 'Total Spent', 'um-woocommerce' ),
		'metakey'           => 'woo_total_spent',
		'type'              => 'text',
		'label'             => __( 'Total Spent', 'um-woocommerce' ),
		'icon'              => 'um-faicon-credit-card',
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	$fields['woo_order_count'] = array(
		'title'             => __( 'Total Orders', 'um-woocommerce' ),
		'metakey'           => 'woo_order_count',
		'type'              => 'text',
		'label'             => __( 'Total Orders', 'um-woocommerce' ),
		'icon'              => 'um-faicon-shopping-cart',
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	$fields = array_merge( $fields, UM()->WooCommerce_API()->api()->get_wc_address_fields() );

	return $fields;
}
add_filter( 'um_predefined_fields_hook', 'um_woocommerce_add_field', 100, 1 );


/**
 * Show total orders
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_profile_field_filter_hook__woo_order_count( $value, $data ) {
	global $wpdb;
	$user_id = um_user('ID');
	$count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*)
			FROM $wpdb->posts as posts 
			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id 
			WHERE meta.meta_key = '_customer_user' AND     
				  posts.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
				  posts.post_status IN ('" . implode( "','", array('wc-completed') )  . "') AND     
				  meta_value = %d",
		$user_id
	) );

	$count = absint( $count );
	if ( $count == 1 ) {
		$output = sprintf( __( '%s order', 'um-woocommerce' ), $count );
	} else {
		$output = sprintf( __( '%s orders', 'um-woocommerce' ), $count );
	}

	return $output;
}
add_filter( 'um_profile_field_filter_hook__woo_order_count', 'um_profile_field_filter_hook__woo_order_count', 99, 2 );


/**
 * Show total spent
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_profile_field_filter_hook__woo_total_spent( $value, $data ) {
	$output = get_woocommerce_currency_symbol() . number_format( wc_get_customer_total_spent( um_user('ID') ) );
	return $output;
}
add_filter( 'um_profile_field_filter_hook__woo_total_spent', 'um_profile_field_filter_hook__woo_total_spent', 99, 2 );


/**
 * Save country to WC fields in register
 *
 * @param $submitted
 * @param $args
 *
 * @return mixed
 */
function um_woocommerce_before_save_filter_submitted( $submitted, $args ) {
	if ( isset( $submitted['billing_country'] ) || isset( $submitted['shipping_country'] ) ) {
		$countries = UM()->builtin()->get( 'countries' );

		if ( isset( $submitted['billing_country'] ) && strlen( $submitted['billing_country'] ) != 2 ) {
			$submitted['billing_country'] = array_search( $submitted['billing_country'], $countries );
		}
		if ( isset( $submitted['shipping_country'] ) && strlen( $submitted['shipping_country'] ) != 2  ) {
			$submitted['shipping_country'] = array_search( $submitted['shipping_country'], $countries );
		}
	}

	return $submitted;
}
add_filter( 'um_before_save_filter_submitted', 'um_woocommerce_before_save_filter_submitted', 10, 2 );


/**
 * Change country to WC fields in profile
 *
 * @param $to_update
 *
 * @return mixed
 */
function um_woocommerce_user_pre_updating_profile( $to_update ) {
	if ( isset( $to_update['billing_country'] ) || isset( $to_update['shipping_country'] ) ) {
		$countries = UM()->builtin()->get( 'countries' );

		if ( isset( $to_update['billing_country'] ) && strlen( $to_update['billing_country'] ) != 2 ) {
			$to_update['billing_country'] = array_search( $to_update['billing_country'], $countries );
		}
		if ( isset( $to_update['shipping_country'] ) && strlen( $to_update['shipping_country'] ) != 2 ) {
			$to_update['shipping_country'] = array_search( $to_update['shipping_country'], $countries );
		}
	}

	return $to_update;
}
add_filter( 'um_user_pre_updating_profile_array', 'um_woocommerce_user_pre_updating_profile', 10, 1 );


/**
 * Enable options pair to WC field country
 *
 * @param null $empty
 * @param $data
 *
 * @return bool|null
 */
function um_woocommerce_select_options_pair( $empty = null, $data ) {
	if ( $data['metakey'] == 'billing_country' || $data['metakey'] == 'shipping_country' ) {
		return true;
	}

	return null;
}
add_filter( 'um_select_options_pair', 'um_woocommerce_select_options_pair', 10, 2 );


/**
 * Show full WC country in profile
 *
 * @param $res
 * @param $data
 *
 * @return mixed
 */
function um_woocommerce_view_field( $res, $data ) {
	if ( strlen( $res ) == 2 && ( $data['metakey'] == 'billing_country' || $data['metakey'] == 'shipping_country' ) ) {
		$countries = UM()->builtin()->get( 'countries' );
		$res = $countries[ $res ];
	}

	return $res;
}
add_filter( 'um_view_field_value_select', 'um_woocommerce_view_field', 10, 2 );


/**
 * @param $skip
 * @param $post_input
 * @param $array
 *
 * @return bool
 */
function um_woocommerce_admin_builder_skip_validation( $skip, $post_input, $array ) {
	// 'billing_country' and 'shipping_country'
	if ( $post_input === '_options' && isset( $array['post']['_metakey'] ) && in_array( $array['post']['_metakey'], array( 'billing_country', 'shipping_country' ) ) ) {
		$skip = true;
	}

	return $skip;
}
add_filter( 'um_admin_builder_skip_field_validation', 'um_woocommerce_admin_builder_skip_validation', 10, 3 );


/**
 * @param $options
 * @param $key
 *
 * @return array
 */
function um_woocommerce_selectbox_options( $options, $key ) {
	// 'billing_country' and 'shipping_country'
	if ( in_array( $key, array( 'billing_country', 'shipping_country' ) ) ) {
		$countries = UM()->builtin()->get( 'countries' );
		if ( empty( $options ) || ! is_array( $options ) ) {
			$options = $countries;
		} else {
			$options = array_intersect_key( $countries, array_flip( $options ) );
		}
	}

	return $options;
}
add_filter( 'um_selectbox_options', 'um_woocommerce_selectbox_options', 10, 2 );


/**
 * Change "billing_state" and "shipping_state" field type to 'select' if options available
 * @since version 2.1.9 [2019-10-24]
 *
 * @param array $array - "billing_state" or "shipping_state" field data
 * @return array
 */
function um_woocommerce_get_field_state( $array ) {

	$country = filter_input( INPUT_POST, 'country', FILTER_SANITIZE_STRING );
	if( empty( $country ) ){
		$country_key = str_replace( 'state', 'country', $array['metakey'] );
		$country = UM()->fields()->field_value( $country_key, WC()->countries->get_base_country() );
	}
	$states = WC()->countries->get_states( $country );

	if ( $states && is_array( $states ) ) {
		$array = array_merge( $array, array(
				'type'     => 'select',
				'input'    => 'select',
				'classes'  => str_replace( 'text', 'select', $array['classes'] ),
				'options'  => $states
		) );
	}
	
	// Enqueue scripts
	wp_enqueue_script( 'um-woocommerce' );

	return $array;
}
add_filter( 'um_get_field__billing_state', 'um_woocommerce_get_field_state' );
add_filter( 'um_get_field__shipping_state', 'um_woocommerce_get_field_state' );
