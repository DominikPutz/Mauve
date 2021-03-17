<?php
/**
 * Template for the UM User Photos, The "Album" block
 *
 * Page: "Profile", tab "Photos"
 * Caller: User_Photos_Ajax->um_user_photos_load_more() method
 * Caller: User_Photos_Ajax->get_um_user_photos_single_album_view() method
 * Patent template: photos.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/single-album.php
 */
if( !defined( 'ABSPATH' ) ) {
	exit;
}

$column = UM()->options()->get( 'um_user_photos_images_column' );
if( !$column ) {
	$column = 'um-user-photos-col-3';
}
$per_row = intval( substr( $column, -1 ) );

if( !empty( $photos ) && is_array( $photos ) ) {
	?>

	<!-- um-user-photos/templates/single-album.php -->
	<div class="um-user-photos-single-album">

		<?php
		for( $i = 0; $i < count( $photos ); $i++ ) {
			$thumbnail_image = wp_get_attachment_image_src( $photos[ $i ], 'gallery_image' );
			if( !$thumbnail_image ) {
				continue;
			}
			$full_image = wp_get_attachment_image_src( $photos[ $i ], 'full' );
			$caption = wp_get_attachment_caption( $photos[ $i ] );
			$img_title = get_the_title( $photos[ $i ] );

			if( !$is_my_profile ) {
				?>

				<div class="um-user-photos-image-block <?php echo esc_attr( $column ); ?>">
					<div class="um-user-photos-image">
						<a data-caption="<?php echo esc_attr( $caption ); ?>" title="<?php echo esc_attr( $img_title ); ?>" href="<?php echo esc_url( $full_image[ 0 ] ); ?>" class="um-user-photos-image" data-id="<?php echo esc_attr( $photos[$i] ); ?>" data-umaction="open_modal">
							<img src="<?php echo esc_url( $thumbnail_image[ 0 ] ); ?>" alt="<?php echo esc_attr( $img_title ); ?>" />
						</a>
					</div>
				</div>

			<?php } else { ?>

				<div class="um-user-photos-image-block um-user-photos-image-block-editable <?php echo esc_attr( $column ); ?>">
					<div class="um-user-photos-image-block-buttons">
						<a 
						   href="javascript:void(0);"
						   data-trigger="um-user-photos-modal" 
						   data-modal_title="<?php esc_attr_e( 'Edit Image', 'um-user-photos' ); ?>" 
						   data-modal_view="album-edit" 
						   class="um-user-photos-add-link" 
						   title="<?php esc_attr_e( 'Edit Image', 'um-user-photos' ); ?>" 
						   data-action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_um_user_photos_view' ) ); ?>" 
						   data-template="modal/edit-image" 
						   data-scope="edit" data-edit="image" 
						   data-id="<?php echo esc_attr( $photos[ $i ] ); ?>" 
						   data-album="<?php echo esc_attr( $album_id ); ?>" >
							<i class="um-faicon-pencil"></i>
						</a>
					</div>
					<div class="um-user-photos-image">
						<a data-caption="<?php echo esc_attr( $caption ); ?>" 
						   data-id="<?php echo esc_attr( $photos[$i] ); ?>"
						   title="<?php echo esc_attr( $img_title ); ?>" 
						   href="<?php echo esc_url( $full_image[ 0 ] ); ?>"
						   class="um-user-photos-image" 
						   data-umaction="open_modal"
						   >
							<img src="<?php echo esc_url( $thumbnail_image[ 0 ] ); ?>" alt="<?php echo esc_attr( $img_title ); ?>" />
						</a>
					</div>
				</div>

				<?php
			}
			if( ($i + 1) % $per_row === 0 ) {
				echo '<div class="um-clear"></div>';
			}
		}
		?>

		<div class="um-clear"></div>
	</div>

<?php } else { ?>
	<p class="text-center"><?php _e( 'Nothing to display', 'um-user-photos' ); ?></p>
	<?php
}

if( $is_my_profile ) {
	UM()->get_template( 'modal/modal.php', um_user_photos_plugin, array(), true );
}