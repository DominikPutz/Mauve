<?php
/**
 * Template for the UM Instagram field, edit mode
 * Used on the "Profile" page or other page with "Instagram Gallery" field type
 * Called from the Instagram_Public->edit_field_profile_instagram_photo() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-instagram/field-edit.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$label = ! empty( $data['label'] ) ? $data['label'] : '';
?>

<div class="um-field um-field-<?php echo $data['type'] ?>" data-key="<?php echo $data['metakey'] ?>">

	<?php echo UM()->fields()->field_label( $label, $data['metakey'], $data ); ?>

	<?php if ( $has_token ) { ?>

		<a href="javascript:void(0);" class="um-ig-photos_disconnect">
			<i class="um-faicon-times"></i><?php _e( 'Disconnect', 'um-instagram' ) ?>
		</a>
		<div class="um-clear"></div>
		<div id="um-ig-content" class="um-ig-content">

			<div id="um-ig-photo-wrap" class="um-ig-photos" data-metakey="<?php echo esc_attr( $data['metakey'] ); ?>" data-user_id="<?php echo esc_attr( um_user( 'ID' ) ); ?>" data-viewing="false"></div>

			<?php echo UM()->Instagram_API()->frontend()->nav_template(); ?>

			<div class="um-clear"></div>

			<?php echo UM()->Instagram_API()->frontend()->get_user_details( $has_token ); ?>

		</div>
		<div id="um-ig-preload"></div>
		<div class="um-clear"></div>
		<input type="hidden" class="um-ig-photos_metakey" name="<?php echo esc_attr( $data['metakey'] ) ?>" value="<?php echo esc_attr( $has_token ) ?>" />

	<?php } else { ?>

		<div class="um-connect-instagram">
			<div class="um-ig-photo-wrap">
				<div class="um-clear"></div>
				<a href="<?php echo esc_url( UM()->Instagram_API()->connect()->connect_url() ) ?>" onclick="window.open( this.href, 'authWindow', 'width=1048,height=690,scrollbars=yes' );return false;">
					<i class="um-faicon-instagram"></i>
					<div class="um-clear"></div>
					<?php _e( 'Connect to Instagram', 'um-instagram' ); ?>
				</a>
			</div>
		</div>

	<?php }

	if ( ! empty( $error_message ) ) { ?>

		<div class="um-field-error">
			<span class="um-field-arrow"><i class="um-faicon-caret-up"></i></span><?php _e( $error_message, 'um-instagram' ); ?>
		</div>

	<?php } ?>

</div>