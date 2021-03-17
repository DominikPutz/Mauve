<?php
namespace um_ext\um_woocommerce\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Main_API
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Main_API {


	/**
	 * WooCommerce_Main_API constructor.
	 */
	function __construct() {

	}


	/**
	 * @param bool $keys_only
	 *
	 * @return array
	 */
	function get_wc_address_fields( $keys_only = false ) {
		$fields = array();

		// billing
		$fields['billing_first_name'] = array(
			'title'     => __( 'WC Billing First name', 'um-woocommerce' ),
			'metakey'   => 'billing_first_name',
			'type'      => 'text',
			'label'     => __( 'WC Billing First name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_last_name'] = array(
			'title'     => __( 'WC Billing Last name', 'um-woocommerce' ),
			'metakey'   => 'billing_last_name',
			'type'      => 'text',
			'label'     => __( 'WC Billing Last name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_company'] = array(
			'title'     => __( 'WC Billing Company', 'um-woocommerce' ),
			'metakey'   => 'billing_company',
			'type'      => 'text',
			'label'     => __( 'WC Billing Company', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_address_1'] = array(
			'title'     => __( 'WC Billing Address 1', 'um-woocommerce' ),
			'metakey'   => 'billing_address_1',
			'type'      => 'text',
			'label'     => __( 'WC Billing Address 1', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_address_2'] = array(
			'title'     => __( 'WC Billing Address 2', 'um-woocommerce' ),
			'metakey'   => 'billing_address_2',
			'type'      => 'text',
			'label'     => __( 'WC Billing Address 2', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_city'] = array(
			'title'     => __( 'WC Billing city', 'um-woocommerce' ),
			'metakey'   => 'billing_city',
			'type'      => 'text',
			'label'     => __( 'WC Billing city', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_postcode'] = array(
			'title'     => __( 'WC Billing postcode', 'um-woocommerce' ),
			'metakey'   => 'billing_postcode',
			'type'      => 'text',
			'label'     => __( 'WC Billing postcode', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_country'] = array(
			'title'     => __( 'WC Billing country', 'um-woocommerce' ),
			'metakey'   => 'billing_country',
			'type'      => 'select',
			'label'     => __( 'WC Billing country', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker',
			'options'   => array_flip ( UM()->builtin()->get( 'countries' ) )
		);

		$fields['billing_state'] = array(
			'title'     => __( 'WC Billing state', 'um-woocommerce' ),
			'metakey'   => 'billing_state',
			'type'      => 'text',
			'label'     => __( 'WC Billing state', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_phone'] = array(
			'title'     => __( 'WC Billing phone', 'um-woocommerce' ),
			'metakey'   => 'billing_phone',
			'type'      => 'text',
			'label'     => __( 'WC Billing phone', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-phone'
		);

		$fields['billing_email'] = array(
			'title'     => __( 'WC Billing email', 'um-woocommerce' ),
			'metakey'   => 'billing_email',
			'type'      => 'text',
			'label'     => __( 'WC Billing email', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-envelope'
		);


		// Shipping
		$fields['shipping_first_name'] = array(
			'title'     => __( 'WC Shipping First name', 'um-woocommerce' ),
			'metakey'   => 'shipping_first_name',
			'type'      => 'text',
			'label'     => __( 'WC Shipping First name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_last_name'] = array(
			'title'     => __( 'WC Shipping Last name', 'um-woocommerce' ),
			'metakey'   => 'shipping_last_name',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Last name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_company'] = array(
			'title'     => __( 'WC Shipping Company', 'um-woocommerce' ),
			'metakey'   => 'shipping_company',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Company', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_address_1'] = array(
			'title'     => __( 'WC Shipping Address 1', 'um-woocommerce' ),
			'metakey'   => 'shipping_address_1',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Address 1', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_address_2'] = array(
			'title'     => __( 'WC Shipping Address 2', 'um-woocommerce' ),
			'metakey'   => 'shipping_address_2',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Address 2', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_city'] = array(
			'title'     => __( 'WC Shipping city', 'um-woocommerce' ),
			'metakey'   => 'shipping_city',
			'type'      => 'text',
			'label'     => __( 'WC Shipping city', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_postcode'] = array(
			'title'     => __( 'WC Shipping postcode', 'um-woocommerce' ),
			'metakey'   => 'shipping_postcode',
			'type'      => 'text',
			'label'     => __( 'WC Shipping postcode', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_country'] = array(
			'title'     => __( 'WC Shipping country', 'um-woocommerce' ),
			'metakey'   => 'shipping_country',
			'type'      => 'select',
			'label'     => __( 'WC Shipping country', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker',
			'options'   => array_flip ( UM()->builtin()->get( 'countries' ) )
		);

		$fields['shipping_state'] = array(
			'title'     => __( 'WC Shipping state', 'um-woocommerce' ),
			'metakey'   => 'shipping_state',
			'type'      => 'text',
			'label'     => __( 'WC Shipping state', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_phone'] = array(
			'title'     => __( 'WC Shipping phone', 'um-woocommerce' ),
			'metakey'   => 'shipping_phone',
			'type'      => 'text',
			'label'     => __( 'WC Shipping phone', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-phone'
		);

		$fields['shipping_email'] = array(
			'title'     => __( 'WC Shipping email', 'um-woocommerce' ),
			'metakey'   => 'shipping_email',
			'type'      => 'text',
			'label'     => __( 'WC Shipping email', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-envelope'
		);

		if ( $keys_only ) {
			return array_keys( $fields );
		} else {
			return $fields;
		}
	}


	/**
	 * Check if Woo Subscriptions plugin is active
	 *
	 * @return bool
	 */
	function is_wc_subscription_plugin_active() {
		return function_exists( 'wcs_get_subscription' );
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	function change_role_data_single( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();
		um_fetch_user( $user_id );

		// fetch role and excluded roles
		$user_role = UM()->user()->get_role();
		$excludes = UM()->options()->get( 'woo_oncomplete_except_roles' );
		$excludes = empty( $excludes ) ? array() : $excludes;

		$data = array();

		//items have more priority
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$id = $item['product_id'];
			if ( get_post_meta( $id, '_um_woo_product_role', true ) != '' && ( empty( $excludes ) || ! in_array( $user_role, $excludes ) ) ) {
				$role = esc_attr( get_post_meta( $id, '_um_woo_product_role', true ) );
				$data = array( 'user_id' => $user_id, 'role' => $role );
			}
		}

		if ( empty( $data ) ) {
			$role = UM()->options()->get( 'woo_oncomplete_role' );
			if ( $role && ! user_can( $user_id, $role ) && ( empty( $excludes ) || ! in_array( $user_role, $excludes ) ) ) {
				return array( 'user_id' => $user_id, 'role' => $role );
			}
		} else {
			return $data;
		}

		return false;
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	function change_role_data_single_refund( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();

		$role = UM()->options()->get( 'woo_onrefund_role' );
		if ( $role && ! user_can( $user_id, $role ) ) {
			return array( 'user_id' => $user_id, 'role' => $role );
		}

		return false;
	}


	/**
	 * Get Order Data via AJAX
	 */
	function ajax_get_order() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['order_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$is_customer = get_post_meta( sanitize_key( $_POST['order_id'] ), '_customer_user', true );

		if ( $is_customer != get_current_user_id() ) {
			wp_send_json_error();
		}
		um_fetch_user( get_current_user_id() );

		$order_id = sanitize_key( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		$notes = $order->get_customer_order_notes();

		$t_args = compact( 'order', 'order_id', 'notes' );
		$output = UM()->get_template( 'order-popup.php', um_woocommerce_plugin, $t_args );

		wp_send_json_success( $output );
	}


	/**
	 * Get Subscription Data via AJAX
	 */
	function ajax_get_subscription() {
		UM()->check_ajax_nonce();

		$subscription = wcs_get_subscription( sanitize_key( $_POST['subscription_id'] ) );
		$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );
		$notes = $subscription->get_customer_order_notes();

		$columns = array(
			'last_order_date_created' => _x( 'Last Order Date', 'admin subscription table header', 'ultimate-member' ),
			'next_payment'            => _x( 'Next Payment Date', 'admin subscription table header', 'ultimate-member' ),
			'end'                     => _x( 'End Date', 'table heading', 'ultimate-member' ),
			'trial_end'               => _x( 'Trial End Date', 'admin subscription table header', 'ultimate-member' ),
		);

		$t_args = compact( 'actions', 'columns', 'notes', 'subscription' );
		$output = UM()->get_template( 'subscription.php', um_woocommerce_plugin, $t_args );

		wp_send_json_success( $output );
	}


	/**
	 * Refresh address via AJAX
	 */
	function ajax_refresh_address() {
		UM()->check_ajax_nonce();

		$country = sanitize_text_field( $_POST['country'] );
		$type = sanitize_key( $_POST['type'] );
		$locale = WC()->countries->get_country_locale();
		if ( isset( $locale[$country]['state']['label'] ) ) {
			$label = $locale[$country]['state']['label'];
		} else {
			$label = __( 'State', 'woocommerce' );
		}

		if ( $type == 'billing_country' ) {
			$country_field = 'billing_country';
			$key = 'billing_state';
			$required = 1;
		} else {
			$country_field = 'shipping_country';
			$key = 'shipping_state';
			$required = 0;
		}

		$html = '';
		$fields = UM()->builtin()->get_specific_fields( $key );
		foreach ( $fields as $key => $data ) {
			$html .= UM()->fields()->edit_field( $key, $data );
		}

		wp_send_json_success( preg_replace( array( '/\r\n/m', '/\t/m', '/\s+/m' ), ' ', $html ) );
	}


	/**
	 * Check if current user has subscriptions and return subscription IDs
	 * @param  integer			$user_id
	 * @param  string				$product_id
	 * @param  string				$status
	 * @param  array|int		$except_subscriptions
	 * @return array|bool		subscription products ids
	 */
	function user_has_subscription( $user_id = 0, $product_id = '', $status = 'any', $except_subscriptions = array() ) {

		if ( ! function_exists('wcs_get_users_subscriptions') ) {
			return '';
		}

		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$has_subscription = false;
		$arr_product_ids = array();
		if ( empty( $product_id ) ) { // Any subscription
			if ( ! empty( $status ) && 'any' != $status ) { // We need to check for a specific status
				foreach ( $subscriptions as $subscription ) {
					if ( in_array( $subscription->get_id(), (array) $except_subscriptions ) ) {
						continue;
					}
					if ( $subscription->has_status( $status ) ) {
						$order_items  = $subscription->get_items();
						foreach ( $order_items as $order ) {
							$arr_product_ids[ ] = wcs_get_canonical_product_id( $order );
						}
					}
				}

				return $arr_product_ids;

			} elseif ( ! empty( $subscriptions ) ) {
				$has_subscription = true;
			}
		} else {
			foreach ( $subscriptions as $subscription ) {
				if ( in_array( $subscription->get_id(), (array) $except_subscriptions ) ) {
					continue;
				}
				if ( $subscription->has_product( $product_id ) && ( empty( $status ) || 'any' == $status || $subscription->has_status( $status ) ) ) {
					$has_subscription = true;
					break;
				}
			}
		}
		return $has_subscription;
	}

}
