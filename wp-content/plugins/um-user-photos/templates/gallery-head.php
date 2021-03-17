<?php
/**
 * Template for the UM User Photos, The "New Album" button
 *
 * Page: "Profile", tab "Photos"
 * Hook: 'ultimatemember_gallery'
 * Caller: User_Photos_Shortcodes->get_gallery_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/gallery-head.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( um_is_myprofile() || $is_my_profile ) {
	?>

	<!-- um-user-photos/templates/gallery-head.php -->
	<div class="text-center um-user-photos-add">
		<a href="javascript:void(0);"
			 data-trigger="um-user-photos-modal"
			 data-modal_title="<?php esc_attr_e( 'New Album', 'um-user-photos' ); ?>"
			 data-modal_view="album-create"
			 class="um-user-photos-add-link um-modal-btn"
			 data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>"
			 data-template="modal/add-album"
			 data-scope="new">
			<i class="um-icon-plus"></i> <?php echo esc_html__( 'New Album', 'um-user-photos' ); ?>
		</a>
	</div>

	<?php
	UM()->get_template( 'modal/modal.php', um_user_photos_plugin, array(), true );
}