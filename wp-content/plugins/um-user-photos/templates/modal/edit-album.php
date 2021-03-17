<?php
/**
 * Template for the UM User Photos, the "Edit Album" modal content
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_ajax_gallery_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/edit-album.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

$image = wp_get_attachment_image_src( get_post_thumbnail_id( $album->ID ), 'album_cover' );
$bg_image = $image[ 0 ];
$photos = get_post_meta( $album->ID, '_photos', true );
?>

<!-- um-user-photos/templates/modal/edit-album.php -->
<form id="um-user-photos-form-edit-album" class="um-user-photos-modal-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=update_um_user_photos_album' ) ); ?>" method="post" enctype="multipart/form-data"  data-max_size_error="<?php esc_attr_e( ' is too large. File should be less than ', 'um-user-photos' ); ?>" data-max_size="<?php echo esc_attr( wp_max_upload_size() ); ?>">

	<div class="um-galley-form-response"></div>

	<div class="um-field">
		<input type="text" placeholder="<?php esc_attr_e( 'Album title', 'um-user-photos' ); ?>" name="album_title" value="<?php echo esc_attr( $album->post_title ); ?>" required="required" />
	</div>

	<div class="um-field">
		<div class="text-center">
			<h1 class="album-poster-holder" style="background-image:url(<?php echo esc_url( $bg_image ); ?>);">
				<label class="album-poster-label">
					<i class="um-faicon-picture-o"></i><br/>
					<span><?php _e( 'Album cover', 'um-user-photos' ); ?></span>
					<input id="um-user-photos-input-album-cover" style="display:none;" type="file" name="album_cover" accept="image/*" />
				</label>
			</h1>
		</div>

		<?php if( !empty( $photos ) && is_array( $photos ) ): ?>
			<div class="um-user-photos-album-photos">
				<?php
				for( $i = 0; $i < count( $photos ); $i++ ):
					$image = wp_get_attachment_image_src( $photos[ $i ], 'thumbnail' );
					if( !$image ) {
						continue;
					}
					?>
					<div class="um-user-photos-photo" id="album-photo-<?php echo esc_attr( $photos[ $i ] ); ?>">
						<p class="image-holder">
							<img src="<?php echo esc_attr( $image[ 0 ] ); ?>"/>
						</p>
						<input type="hidden" name="photos[]" value="<?php echo esc_attr( $photos[ $i ] ); ?>"/>
						<a class="photo-delete-link um-tip-n"
							 href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=um_delete_album_photo' ) ); ?>"
							 data-id="<?php echo esc_attr( $photos[ $i ] ); ?>"
							 data-album="<?php echo esc_attr( $album->ID ); ?>"
							 data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'um_delete_photo' ) ); ?>"
							 original-title="<?php esc_attr_e( 'Delete photo', 'um-user-photos' ); ?>"
							 data-confirmation="<?php esc_attr_e( 'Sure to delete photo?', 'um-user-photos' ); ?>"
							 data-delete_photo="#album-photo-<?php echo esc_attr( $photos[ $i ] ); ?>"
							 ><i class="um-faicon-times"></i></a>
					</div>
					<?php
				endfor;
				?>
				<div class="um-clear"></div>
			</div>
			<div class="um-clear"></div>
		<?php endif; ?>

		<div id="um-user-photos-images-uploaded"></div>
		<div class="um-clear"></div>
	</div>

	<div class="um-field um-user-photos-modal-footer text-right">
		<button id="um-user-photos-album-update" class="um-modal-btn" data-album_action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_single_album_view' ) ); ?>"><?php _e( 'Update', 'um-user-photos' ); ?></button>
		<label class="um-modal-btn alt">
			<i class="um-icon-plus"></i> <?php _e( 'Add photos', 'um-user-photos' ); ?>
			<input id="um-user-photos-input-album-images" style="display:none;" type="file" name="album_images[]" accept="image/*" multiple/>
		</label>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?php esc_html_e( 'Cancel', 'um-user-photos' ); ?></a>
	</div>

	<input type="hidden" name="album_id" value="<?php echo esc_attr( $album->ID ); ?>"/>
	<?php wp_nonce_field( 'um_edit_album' ); ?>
</form>