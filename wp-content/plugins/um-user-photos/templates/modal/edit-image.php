<?php
/**
 * Template for the UM User Photos, the "Edit Image" modal content
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_ajax_gallery_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/edit-image.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

$disable_comment = get_post_meta($photo->ID,'_disable_comment',true);
?>

<!-- um-user-photos/templates/modal/edit-image.php -->
<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=update_um_user_photos_image' ) ); ?>" enctype="multipart/form-data">

	<div class="um-galley-form-response"></div>

	<div class="um-field">
		<input type="text" name="title" placeholder="<?php esc_attr_e( 'Image title', 'um-user-photos' ); ?>" value="<?php echo esc_attr( $photo->post_title ); ?>" required="required" />
	</div>

	<div class="um-field">
		<textarea name="caption" placeholder="<?php esc_attr_e( 'Image caption', 'um-user-photos' ); ?>" required="required" ><?php echo esc_html( $photo->post_excerpt ); ?></textarea>
	</div>
	
	<div class="um-field">
		<p>
			<label>
				<input 
					   type="checkbox" 
					   name="disable_comments"
					   <?php if($disable_comment){ echo 'checked'; } ?>
					   />
				<?php _e('Disable comments','um-user-photos'); ?>
			</label>
		</p>
	</div>

	<div class="um-field um-user-photos-modal-footer text-right">
		<button type="button" id="um-user-photos-image-update-btn" class="um-modal-btn um-galley-modal-update"><?php _e( 'Update', 'um-user-photos' ); ?></button>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php _e( 'Cancel', 'um-user-photos' ); ?></a>
	</div>

	<input type="hidden" name="id" value="<?php echo esc_attr( $photo->ID ); ?>"/>
	<input type="hidden" name="album" value="<?php echo esc_attr( $album->ID ); ?>"/>
	<?php wp_nonce_field( 'um_edit_image' ); ?>
</form>