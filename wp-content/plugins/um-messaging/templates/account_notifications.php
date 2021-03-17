<?php
/**
 * Template for the UM Private Messages.
 * Used on the "Account" page, "Notifications" tab
 *
 * Caller: method Messaging_Account->account_tab()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-messaging/account_notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-field-area">
	<label class="um-field-checkbox <?php if ( ! empty( $_enable_new_pm ) ) { ?>active<?php } ?>">
		<input type="checkbox" name="_enable_new_pm" value="1" <?php checked( ! empty( $_enable_new_pm ) ) ?> />
		<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-<?php if ( ! empty( $_enable_new_pm ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i></span>
		<span class="um-field-checkbox-option"><?php echo __( 'Someone sends me a private message', 'um-messaging' ); ?></span>
	</label>

	<div class="um-clear"></div>
</div>
