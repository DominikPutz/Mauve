<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Purchases tab
 *
 * @param $args
 */
function um_profile_content_purchases_default( $args ) {

	$loop = new WP_Query( array(
			'post_type'			 => 'product',
			'posts_per_page' => -1
			) );

	if( $loop->found_posts ) {

		$t_args = compact( 'args', 'loop' );
		UM()->get_template( 'my-purchases.php', um_woocommerce_plugin, $t_args, true );

	} else { ?>

		<div class="um-profile-note">
			<span><?php echo ( um_profile_id() == get_current_user_id() ) ? __( 'You did not purchase any product yet.', 'um-woocommerce' ) : __( 'User did not purchase any product yet.', 'um-woocommerce' ); ?></span>
		</div>

	<?php }

	wp_enqueue_script( 'um-woocommerce' );
	wp_enqueue_style( 'um-woocommerce' );
}
add_action( 'um_profile_content_purchases_default', 'um_profile_content_purchases_default', 10, 1 );


/**
 * Product reviews tab
 *
 * @param $args
 */
function um_profile_content_product_reviews_default( $args ) {

	$comments = get_comments( array(
		'post_type' => 'product',
		'user_id'   => um_profile_id()
	) );

	if ( $comments ) {

		$t_args = compact( 'args', 'comments' );
		UM()->get_template( 'product-reviews.php', um_woocommerce_plugin, $t_args, true );

	} else { ?>

		<div class="um-profile-note">
			<span>
				<?php echo ( um_profile_id() == get_current_user_id() ) ? __('You did not review any products yet.','um-woocommerce') : __('User did not review any product yet.','um-woocommerce'); ?>
			</span>
		</div>

	<?php }

	wp_enqueue_script( 'um-woocommerce' );
	wp_enqueue_style( 'um-woocommerce' );
}
add_action('um_profile_content_product-reviews_default', 'um_profile_content_product_reviews_default');


/**
 *
 */
function um_woo_account_move_subscription_info() {
	remove_action ('woocommerce_before_my_account','WC_Subscriptions::get_my_subscriptions_template' ); 
	add_action( 'woocommerce_add_subscriptions_to_my_account', 'WC_Subscriptions::get_my_subscriptions_template' ); 
	//add_action( 'woocommerce_add_subscriptions_to_my_account', array('WC_Memberships_Frontend','my_account_memberships') ); 

	if ( function_exists( 'wc_memberships' ) ) { 
		remove_action( 'woocommerce_before_my_account', array('WC_Memberships_Member_Area', 'my_account_memberships' ) ); 
	} 
}
add_action( 'init', 'um_woo_account_move_subscription_info' );