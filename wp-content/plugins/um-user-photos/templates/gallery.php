<?php
/**
 * Template for the UM User Photos, The "Albums" block
 *
 * Page: "Profile", tab "Photos"
 * Hook: 'ultimatemember_gallery'
 * Caller: User_Photos_Shortcodes->get_gallery_content() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/gallery.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( $albums->have_posts() ) {
	?>

	<!-- um-user-photos/templates/gallery.php -->
	<div class="um-user-photos-albums">

		<?php
		while ( $albums->have_posts() ) {
			$albums->the_post();
			$photos = get_post_meta( get_the_ID(), '_photos', true );
			if ( $photos ) {
				$count = count( $photos );
				$count_msg = sprintf( _n( '%s Photo', '%s Photos', $count, 'um-user-photos' ), number_format_i18n( $count ) );
			}
			else {
				$count_msg = false;
			}

			$data_t = array(
				'id'				 => get_the_ID(),
				'title'			 => get_the_title(),
				'count_msg'	 => $count_msg
			);
			UM()->get_template( 'album-block.php', um_user_photos_plugin, $data_t, true );
		}
		wp_reset_postdata();
		?>

		<div class="um-clear"></div>
	</div>

	<?php
}
else {
	?>
	<p class="text-center"><?php _e( 'Nothing to display', 'um-user-photos' ) ?></p>
	<?php
}