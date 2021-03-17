<?php
/**
 * Template for the UM Verified Users.
 * Used on the "Account" page, "Get Verified" account field
 *
 * Caller: function um_verified_account_info()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-verified-users/account_info.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-field">

	<div class="um-field-label">
		<label><?php _e( 'Get Verified', 'um-verified' ) ?></label>
		<div class="um-clear"></div>
	</div>

	<?php if ( $verified_status == 'unverified' ) { ?>

		<div class="um-verified-info"><a href="<?php echo esc_url( UM()->Verified_Users_API()->api()->verify_url( $user_id, um_get_core_page( 'account' ) ) ) ?>" class="um-link um-verified-request-link"><?php _e( 'Request Verification', 'um-verified' ) ?></a></div>

	<?php } elseif( $verified_status == 'pending' ) { ?>

		<div class="um-verified-info"><?php printf( __( 'Your verification request is currently pending. <a href="%s" class="um-verified-cancel-request">Cancel request?</a>', 'um-verified' ), esc_url( $cancel ) ) ?></div>

	<?php } ?>

</div>