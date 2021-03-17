<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * When any product is on-hold purchase
 *
 * @param $order_id
 *
 * @return mixed
 */
function um_woocommerce_sync_role_onhold( $order_id ) {
	$change = UM()->options()->get( 'woo_onhold_change_roles' );

	if ( !$change ) {
		return $order_id;
	}

	$change_role = UM()->WooCommerce_API()->api()->change_role_data_single( $order_id );
	if ( !$change_role ) {
		return $order_id;
	}

	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();
	$userdata = get_userdata( $user_id );
	$old_roles = $userdata->roles;
	update_user_meta( $user_id, 'um_woo_change_role_' . $order_id, $old_roles );

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$remove_previous = UM()->options()->get( 'woo_remove_roles' );

	$role = $change_role['role'];
	$user_id = $change_role['user_id'];
	$userdata = get_userdata( $user_id );
	$old_roles = $userdata->roles;

	UM()->roles()->set_role( $user_id, $role );

	foreach ( $old_roles as $_role ) {
		if ( $role == $_role ) {
			continue;
		}
		UM()->roles()->remove_role( $user_id, $_role );
		if ( !$remove_previous ) {
			UM()->roles()->set_role_wp( $user_id, $_role );
		}
	}

	do_action( 'um_after_member_role_upgrade', array($role), $old_roles, $user_id );

	$auto_approve = apply_filters( 'um_woocommerce_auto_approve_on_completed', true );
	if ( $auto_approve ) {
		UM()->user()->approve( false );
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );

	return $order_id;
}

add_action( 'woocommerce_order_status_on-hold', 'um_woocommerce_sync_role_onhold' );

/**
 * @param $order_id
 *
 * @return mixed
 */
function um_woocommerce_sync_role_onrefund( $order_id ) {
	$change_role = UM()->WooCommerce_API()->api()->change_role_data_single_refund( $order_id );

	if ( !$change_role ) {
		return $order_id;
	}

	$remove_previous = UM()->options()->get( 'woo_remove_roles' );

	$role = $change_role['role'];
	$user_id = $change_role['user_id'];
	$userdata = get_userdata( $user_id );
	$old_roles = $userdata->roles;

	UM()->roles()->set_role( $user_id, $role );

	foreach ( $old_roles as $_role ) {
		if ( $role == $_role ) {
			continue;
		}

		UM()->roles()->remove_role( $user_id, $_role );
		if ( !$remove_previous ) {
			UM()->roles()->set_role_wp( $user_id, $_role );
		}
	}

	do_action( 'um_after_member_role_upgrade', array($role), $old_roles, $user_id );

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );

	return $order_id;
}

add_action( 'woocommerce_order_status_refunded', 'um_woocommerce_sync_role_onrefund' );

/**
 * @param $order_id
 *
 * @return mixed
 */
function um_woocommerce_sync_role_failed( $order_id ) {
	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();

	$change = UM()->options()->get( 'woo_onhold_change_roles' );
	$previous_roles = get_user_meta( $user_id, 'um_woo_change_role_' . $order_id );

	if ( !empty( $previous_roles ) && $change ) {
		delete_user_meta( $user_id, 'um_woo_change_role_' . $order_id );

		// Disable social activity 'joined site' post
		remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
		// Disable welcome email
		add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

		$userdata = get_userdata( $user_id );
		$old_roles = $userdata->roles;

		foreach ( $old_roles as $_role ) {
			UM()->roles()->remove_role( $user_id, $_role );
		}

		foreach ( $previous_roles as $_role ) {
			UM()->roles()->set_role_wp( $user_id, $_role );
		}

		do_action( 'um_after_member_role_upgrade', $previous_roles, $old_roles, $user_id );

		// forcefully flush the cache
		UM()->user()->remove_cache( $user_id );
	}

	return $order_id;
}

add_action( 'woocommerce_order_status_failed', 'um_woocommerce_sync_role_failed' );
add_action( 'woocommerce_order_status_canceled', 'um_woocommerce_sync_role_failed' );

/**
 * When any product is bought
 *
 * @param $order_id
 *
 * @return mixed
 */
function um_woocommerce_sync_role_completed( $order_id ) {
	if ( class_exists( 'WC_Subscriptions' ) && function_exists( 'wcs_get_subscription_status_name' ) ) {
		$subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) );
		foreach ( $subscriptions as $subscription_id => $subscription ) {
			$subscription_status = wcs_get_subscription_status_name( $subscription->get_status() );
			if ( in_array( $subscription_status, array( 'On hold', 'Active' ) ) ) {
				return $order_id;
			}
		}
	}

	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();
	$change = UM()->options()->get( 'woo_onhold_change_roles' );
	$previous_role = get_user_meta( $user_id, 'um_woo_change_role_' . $order_id );

	$remove_previous = UM()->options()->get( 'woo_remove_roles' );

	if ( ! empty( $previous_role ) && $change ) {
		delete_user_meta( $user_id, 'um_woo_change_role_' . $order_id );
		return $order_id;
	}

	$change_role = UM()->WooCommerce_API()->api()->change_role_data_single( $order_id );

	if ( ! $change_role ) {
		return $order_id;
	}

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$role = $change_role['role'];
	$user_id = $change_role['user_id'];
	$userdata = get_userdata( $user_id );
	$old_roles = $userdata->roles;

	UM()->roles()->set_role( $user_id, $role );
	foreach ( $old_roles as $_role ) {
		if ( $role == $_role ) {
			continue;
		}
		UM()->roles()->remove_role( $user_id, $_role );
		if ( !$remove_previous ) {
			UM()->roles()->set_role_wp( $user_id, $_role );
		}
	}

	do_action( 'um_after_member_role_upgrade', array($role), $old_roles, $user_id );

	$auto_approve = apply_filters( 'um_woocommerce_auto_approve_on_completed', true );
	if ( $auto_approve ) {
		UM()->user()->approve( false );
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );

	return $order_id;
}

add_action( 'woocommerce_order_status_completed', 'um_woocommerce_sync_role_completed' );
add_action( 'woocommerce_order_status_processing', 'um_woocommerce_sync_role_completed' );

/**
 * Subscription change status
 *
 * @param $subscription_id
 * @param $old_status
 * @param $new_status
 */
function um_woocommerce_subscription_status_changed( $subscription_id, $old_status, $new_status ) {

	if ( !function_exists( 'wcs_get_subscription' ) ) {
		return;
	}

	$subscription = wcs_get_subscription( $subscription_id );
	$user_id = $subscription->get_user_id();

	$excludes = UM()->options()->get( 'woo_oncomplete_except_roles' );
	if ( !is_array( $excludes ) ) {
		$excludes = array();
	}

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$arr = array(
		'active'				 => '_um_woo_product_activated_role',
		'pending'				 => '_um_woo_product_downgrade_pending_role',
		'on-hold'				 => '_um_woo_product_downgrade_onhold_role',
		'expired'				 => '_um_woo_product_downgrade_expired_role',
		'cancelled'			 => '_um_woo_product_downgrade_cancelled_role',
		'pending-cancel' => '_um_woo_product_downgrade_pendingcancel_role',
	);

	um_fetch_user( $user_id );
	$userdata = $subscription->get_user();
	$old_roles = $userdata->roles;


	// Check if current user has subscriptions
	// and return subscription's products IDs or FALSE
	$has_subscription_product_ids = UM()->WooCommerce_API()->api()->user_has_subscription( $user_id, '', 'active', $subscription->get_id() );

	// Option "Remove previous roles when change role on complete or refund payment"
	$remove_previous = UM()->options()->get( 'woo_remove_roles' );

	// Remove previous added role from other subscription products
	if( $remove_previous && $has_subscription_product_ids && is_array( $has_subscription_product_ids ) ){
		foreach ( $has_subscription_product_ids as $product_id ) {
			foreach ( $arr as $mkey ) {
				$old_single_role = esc_attr( get_post_meta( $product_id, $mkey, true ) );
				if( $old_single_role ){
					UM()->roles()->remove_role( $user_id, $old_single_role );
				}
			}
		}
	}


	// Check all products in the subscription and change a role if necessary
	$items = $subscription->get_items();

	foreach ( $items as $item ) {
		$id = $item['product_id'];

		$new_single_role = $old_single_role = '';

		if ( isset( $arr[$new_status] ) ) {
			$new_single_role = get_post_meta( $id, $arr[$new_status], true );
		}
		if ( isset( $arr[$old_status] ) ) {
			$old_single_role = get_post_meta( $id, $arr[$old_status], true );
		}

		if ( empty( $new_single_role ) || $new_single_role === $old_single_role ) {
			continue;
		}

		if ( !in_array( $new_single_role, $excludes ) ) {
			$role = esc_attr( $new_single_role );

			UM()->roles()->set_role( $user_id, $role );

			do_action( 'um_after_member_role_upgrade', array($role), $old_roles, $user_id );
		}

		if ( !in_array( $old_single_role, $excludes ) ) {
			UM()->roles()->remove_role( $user_id, $old_single_role );
		}
	}


	// Possible statuses: pending, active, on-hold, pending-cancel, cancelled, switched or expired
	switch ( $new_status ) {
		case 'active':
			$auto_approve = apply_filters( "um_woocommerce_auto_approve_on_completed", true );
			if ( $auto_approve ) {
				UM()->user()->approve( false );
			}
			break;

		case 'on-hold':
		case 'expired':
		case 'pending':
		case 'pending-cancel':
		case 'cancelled':
			break;
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}

add_action( 'woocommerce_subscription_status_changed', 'um_woocommerce_subscription_status_changed', 10, 3 );

/**
 * Save old and new status
 * @param string $old_status
 * @param string $new_status
 * @param WC_Subscription $subscription
 */
function um_woocommerce_subscription_pre_update_status( $old_status, $new_status, $subscription ) {
	if ( is_object( $subscription ) ) {
		update_post_meta( $subscription->get_id(), 'um_old_status', $old_status );
		update_post_meta( $subscription->get_id(), 'um_new_status', $new_status );
	}
}

add_action( 'woocommerce_subscription_pre_update_status', 'um_woocommerce_subscription_pre_update_status', 20, 3 );


/**
 * Disable 'WooCommerce Subscriptions' role switcher
 * @see /wp-content/plugins/woocommerce-subscriptions/includes/wcs-user-functions.php
 * @see WC_Subscription::update_status()
 */
add_filter( 'woocommerce_subscriptions_update_users_role', function() {
	return false;
}, 20 );
