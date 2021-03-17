<?php
/**
 * Template for the UM User Photos, The "Photos" block
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Shortcodes->gallery_photos_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/photos.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !empty( $photos ) && is_array( $photos ) ) {
	?>

	<!-- um-user-photos/templates/photos.php -->
	<div class="um-user-photos-albums">
		<div class="photos-container">

			<?php
			$args_t = compact( 'is_my_profile', 'photos' );
			UM()->get_template( 'single-album.php', um_user_photos_plugin, $args_t, true );
			?>

		</div>

		<?php if( $count > $per_page ) { ?>
			<div class="um-load-more">
				<div class="um-clear">
					<hr/>
				</div>
				<p class="text-center">
				<button id="um-user-photos-toggle-view-photos-load-more" data-href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=um_user_photos_load_more' ) ); ?>" class="um-modal-btn alt" data-current_page="1" data-per_page="<?php echo esc_attr( $per_page ); ?>" data-profile="<?php echo esc_attr( $user_id ); ?>"><?php _e( 'Load more', 'um-user-photos' ); ?></button>
				</p>
			</div>
		<?php } ?>

		<div class="um-clear"></div>
	</div>

<?php } else { ?>
	<p class="text-center"><?php _e( 'Nothing to display', 'um-user-photos' ) ?></p>
	<?php
}