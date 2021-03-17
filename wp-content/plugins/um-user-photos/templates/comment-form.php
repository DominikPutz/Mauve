<?php
/**
 * Template for the UM User Photos, The single "Album" block
 *
 * Page: "Profile", tab "Photos"
 * Parent template: gallery.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/album-block.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() && UM()->Photos_API()->can_comment() ) { ?>

		<div class="um-user-photos-commentl um-user-photos-comment-area">
			<div class="um-user-photos-comment-avatar">
				<?php echo get_avatar( get_current_user_id(), 80 ); ?>
			</div>
			<div class="um-user-photos-comment-box">
				<textarea class="um-user-photos-comment-textarea"
				          data-replytext="<?php esc_attr_e('Write a reply...','um-user-photos'); ?>"
				          data-reply_to="0"
						  data-image="<?php echo esc_attr( $image_id ); ?>"
				          placeholder="<?php esc_attr_e('Write a comment...','um-user-photos'); ?>"></textarea>
			</div>
			<div class="um-user-photos-right">
				<a href="javascript:void(0);" class="um-button um-user-photos-comment-post um-disabled">
					<?php _e( 'Comment', 'um-user-photos' ); ?>
				</a>
			</div>
			<div class="um-clear"></div>
		</div>

<?php }