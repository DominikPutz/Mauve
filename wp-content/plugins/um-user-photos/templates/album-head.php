<?php
/**
 * Template for the UM User Photos, The "Album header" block
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->get_um_user_photos_single_album_view() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/album-head.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- um-user-photos/templates/album-head.php -->
<div class="um-user-photos-album-head">
	<div class="col-back">
		<a href="javascript:void(0);"
			 class="back-to-um-user-photos"
			 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
			 data-template="gallery"
			 data-profile="<?php echo esc_attr( $album->post_author ); ?>"
			 >
			<span class="um-icon-arrow-left-c"></span> <?php _e( 'Back', 'um-user-photos' ); ?>
		</a>
	</div>

	<div class="col-title">
		<h2><?php echo esc_html( $album->post_title ); ?></h2>
	</div>

	<div class="col-delete">
		<?php if( $is_my_profile ) { ?>
			<a href="" class="um-user-photos-album-options"><i class="um-faicon-cog"></i></a>
			<div class="um-dropdown">
				<div class="um-dropdown-b">
					<div class="um-dropdown-arr"><i class="um-icon-arrow-up-b"></i></div>
					<ul>
						<li>
							<a href="javascript:void(0);"
								 data-trigger="um-user-photos-modal"
								 data-modal_title="Edit Album"
								 data-modal_view="album-edit"
								 data-original-title="Edit Album"
								 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
								 data-template="modal/edit-album"
								 data-scope="edit"
								 data-edit="album"
								 data-id="<?php echo esc_attr( $album_id ); ?>"
								 >
									 <?php _e( 'Edit album', 'um-user-photos' ); ?>
							</a>
						</li>
						<li>
							<a href="javascript:void(0);"
								 data-trigger="um-user-photos-modal"
								 data-modal_title="<?php esc_attr_e( 'Delete album', 'um-user-photos' ); ?>"
								 data-modal_view="album-delete"
								 data-original-title="<?php esc_attr_e( 'Delete album', 'um-user-photos' ); ?>"
								 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
								 data-template="modal/delete-album"
								 data-scope="edit"
								 data-edit="album"
								 data-id="<?php echo esc_attr( $album_id ); ?>"
								 >
									 <?php esc_html_e( 'Delete album', 'um-user-photos' ); ?>
							</a>
						</li>
						<li><a href="javascript:void(0);" class="um-dropdown-hide">Cancel</a></li>
					</ul>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>
</div>