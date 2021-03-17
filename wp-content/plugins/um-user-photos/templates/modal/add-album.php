<?php
/**
 * Template for the UM User Photos, the "New Album" modal content
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_ajax_gallery_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/add-album.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-user-photos/templates/modal/add-album.php -->
<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=create_um_user_photos_album' ) ); ?>" enctype="multipart/form-data" class="um-user-photos-modal-form"  data-max_size_error="<?php _e( 'is too large. File should be less than ', 'um-user-photos' ); ?>" data-max_size="<?php echo esc_attr( wp_max_upload_size() ); ?>">

	<div class="um-galley-form-response"></div>

	<div class="um-field">
		<input type="text" name="title" placeholder="<?php _e( 'Album title', 'um-user-photos' ); ?>" required/>
	</div>

	<div class="um-field">
		<div class="text-center">
			<h1 class="album-poster-holder">
				<label class="album-poster-label">
					<i class="um-faicon-picture-o"></i><br/>
					<span><?php _e( 'Album cover', 'um-user-photos' ); ?></span>
					<input id="um-user-photos-input-album-cover" style="display:none;" type="file" name="album_cover" accept="image/*" />
				</label>
			</h1>
		</div>

		<div id="um-user-photos-images-uploaded"></div>
		<div class="clearfix"></div>
	</div>

	<div class="um-field um-user-photos-modal-footer text-right">
		<button type="button" class="um-modal-btn um-galley-modal-submit"><?php _e( 'Add', 'um-user-photos' ); ?></button>
		<label class="um-modal-btn alt">
			<i class="um-icon-plus"></i>
			<?php _e( 'Add photos', 'um-user-photos' ); ?>
			<input id="um-user-photos-input-album-images" style="display:none;" type="file" name="album_images[]" accept="image/*" multiple />
		</label>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php _e( 'Cancel', 'um-user-photos' ); ?></a>
	</div>
	<?php wp_nonce_field( 'um_add_album' ); ?>
</form>