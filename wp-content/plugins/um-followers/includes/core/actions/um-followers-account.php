<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * hook in account update to subscribe/unsubscribe users
 */
function um_followers_account_update() {
	/**
	 * issue helpscout#31301
	 */
	$current_tab = filter_input( INPUT_POST, '_um_account_tab' );
	if( 'notifications' !== $current_tab ){
		return;
	}

	$user_id = um_user( 'ID' );

	if ( isset( $_POST['_enable_new_follow'] ) ) {
		update_user_meta( $user_id, '_enable_new_follow', 'yes' );
	} else {
		update_user_meta( $user_id, '_enable_new_follow', 'no' );
	}
}
add_action( 'um_post_account_update', 'um_followers_account_update' );