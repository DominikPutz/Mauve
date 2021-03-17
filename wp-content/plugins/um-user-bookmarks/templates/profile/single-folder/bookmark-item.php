<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-user-bookmarked-item <?php echo esc_attr( $has_image_class ); ?>">
	<div target="_blank" class="um-user-bookmarkss-list" href="<?php echo esc_url( $post_link ); ?>">
		<?php if ( $has_image ) { ?>
			<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
				<img class="um-user-bookmarked-post-image" src="<?php echo $image[0]; ?>" alt="" />
			</a>
		<?php } ?>

		<div class="um-user-bookmarks-post-content">
			<h3><a href="<?php echo esc_url( $post_link ); ?>" target="_blank"><?php echo $post_title; ?></a></h3>

			<?php if ( trim( $excerpt ) != '' ) { ?>
				<p style="margin-bottom:0;"><?php echo $excerpt; ?>...</p>
			<?php }

			if ( is_user_logged_in() && um_profile_id() == get_current_user_id() && $id ) { ?>
				<a href="javascript:void(0);" data-nonce="<?php echo wp_create_nonce('um_user_bookmarks_remove_' . $id ); ?>"
				   data-remove_element="true"
				   class="um-user-bookmarks-profile-remove-link"
				   data-id="<?php echo esc_attr( $id ); ?>">
					<?php _e( 'Remove', 'um-user-bookmarks' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>

	<div class="um-clear"></div>
	<hr/>
</div>