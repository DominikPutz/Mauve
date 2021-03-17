<?php
/**
 * Template for the UM Instagram field, user details section
 * Used on the "Profile" page or other page with "Instagram Gallery" field type
 * Called from the Instagram_Public->get_user_details() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-instagram/user-details.php
 */
if ( !defined( 'ABSPATH' ) ) exit;
?>

<?php if ( !empty( $error ) && um_is_myprofile() ) { ?>
	<div class="um-ig-update">
		<a href="<?php echo esc_url( UM()->Instagram_API()->connect()->connect_url() ) ?>" onclick="window.open(this.href, 'authWindow', 'width=1048,height=690,scrollbars=yes');return false;" title="<?php esc_attr_e( 'Get new photos from Instagram', 'um-instagram' ); ?>">
			<i class="um-faicon-instagram"></i>&nbsp;<?php _e( 'Update', 'um-instagram' ); ?>
		</a>
	</div>
<?php } ?>

<div class="um-ig-footer">
	<div class="um-ig-user-details">
		<a href="<?php echo esc_url( 'https://instagram.com/' . $user->username ) ?>/" target="_blank" rel="nofollow">
			<i class="um-faicon-instagram"></i>&nbsp;
			<span><?php _e( 'View all photos on Instagram', 'um-instagram' ) ?></span>
		</a>
	</div>

	<div class="um-ig-paginate">
		<span>0/0</span>
	</div>
</div>