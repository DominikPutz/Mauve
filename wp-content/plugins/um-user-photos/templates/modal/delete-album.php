<?php
/**
 * Template for the UM User Photos, the "Delete Album" modal content
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_ajax_gallery_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/modal/delete-album.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-user-photos/templates/modal/delete-album.php -->
<form>
	<div class="um-galley-form-response"></div>
	<h3 class="text-center" style="padding-top:0;margin-top:0;">
		<?php _e( 'Are you sure to delete this album?', 'um-user-photos' ); ?>
	</h3>
	<div class="clearfix"></div>
	<div class="um-user-photos-modal-footer text-right">

		<?php if( !empty( $album ) ) : ?>
			<button
				id="delete-um-album"
				class="um-modal-btn"
				data-id="<?php echo esc_attr( $album->ID ); ?>"
				data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'um_delete_album' ) ); ?>"
				data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=delete_um_user_photos_album' ) ); ?>"
				><?php esc_html_e( 'Delete', 'um-user-photos' ); ?></button>
			<?php endif; ?>

		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link">
			<?php esc_html_e( 'Cancel', 'um-user-photos' ); ?>
		</a>
	</div>
</form>