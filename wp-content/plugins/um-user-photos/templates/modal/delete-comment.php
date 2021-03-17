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
	<h3 class="text-center" style="padding-top:0;margin-top:0;font-size:17px;margin-bottom:20px;">
		<?php echo esc_html( $message ); ?>
	</h3>
	<div class="clearfix"></div>
	<div class="um-user-photos-modal-footer text-right">

		<?php if( !empty( $comment ) ) : ?>
			<button
				id="delete-um-user-photos-comment"
				class="um-modal-btn"
				data-id="<?php echo esc_attr( $comment->comment_ID ); ?>"
				data-action="um_user_photos_delete_comment"
				><?php _e( 'Delete', 'um-user-photos' ); ?></button>
		<?php endif; ?>

		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link">
			<?php _e( 'Cancel', 'um-user-photos' ); ?>
		</a>
	</div>
</form>