<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add tab for product reviews
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_woocommerce_add_tab( $tabs ) {
	$tabs['purchases'] = array(
		'name' => __( 'Purchases', 'um-woocommerce' ),
		'icon' => 'um-faicon-shopping-cart'
	);

	$tabs['product-reviews'] = array(
		'name' => __( 'Product Reviews', 'um-woocommerce' ),
		'icon' => 'um-faicon-star'
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_woocommerce_add_tab', 800 );


/**
 * Add tabs based on user
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_woocommerce_user_add_tab( $tabs ) {
	if ( ! empty( $tabs['purchases'] ) && ! um_user( 'woo_purchases_tab' ) ) {
		unset( $tabs['purchases'] );
	}

	if ( ! empty( $tabs['product-reviews'] ) && ! um_user( 'woo_reviews_tab' ) ) {
		unset( $tabs['product-reviews'] );
	}

	return $tabs;

}
add_filter( 'um_user_profile_tabs', 'um_woocommerce_user_add_tab', 1000, 1 );