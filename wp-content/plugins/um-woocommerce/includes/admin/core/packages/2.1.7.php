<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$get_form_fields = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_um_custom_fields'", ARRAY_A );
if ( ! empty( $get_form_fields ) ) {
	foreach ( $get_form_fields as $field ) {
		$id = $field['post_id'];
		$meta_value = unserialize( $field['meta_value'] );

		if ( isset( $meta_value['billing_country'] ) || isset( $meta_value['shipping_country'] ) ) {
			if ( ! empty( $meta_value['billing_country'] ) ) {
				$meta_value['billing_country']['type'] = 'select';
			}
			if ( ! empty( $meta_value['shipping_country'] ) ) {
				$meta_value['shipping_country']['type'] = 'select';
			}
			update_post_meta( $id, '_um_custom_fields', $meta_value );
		}

	}
}

$get_user_meta = $wpdb->get_results( "SELECT user_id, meta_value, meta_key FROM {$wpdb->usermeta} WHERE meta_key = 'billing_country' OR meta_key = 'shipping_country'", ARRAY_A );
if ( ! empty( $get_user_meta ) ) {
	foreach ( $get_user_meta as $field ) {
		$countries = UM()->builtin()->get( 'countries' );
		$country_field = $field['meta_value'];
		$meta_key = $field['meta_key'];

		if ( strlen( $country_field ) != 2 ) {
			if ( in_array( $country_field, $countries ) ) {
				$country_code = array_search( $country_field, $countries );
				update_user_meta( $field['user_id'], $meta_key, $country_code );
			} else {
				$country_field = remove_accents( $country_field );
				if ( in_array( $country_field, $countries ) ) {
					$country_code = array_search( $country_field, $countries );
					update_user_meta( $field['user_id'], $meta_key, $country_code );
				}
			}
			delete_option( "um_cache_userdata_{$field['user_id']}" );
		}
	}
}