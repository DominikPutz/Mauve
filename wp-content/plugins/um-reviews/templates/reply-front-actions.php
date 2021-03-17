<?php
/**
 * Template for the UM User Reviews, The "Reply actions" block
 *
 * Page: "Profile", tab "Reviews"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/reply-front-actions.php
 */

if ( !defined( 'ABSPATH' ) ) exit;


if ( is_user_logged_in() && get_current_user_id() == $profile_id ) { ?>

	<div class="um-reviews-reply-note"></div>

	<div class="um-reviews-reply-tools">
		<div class="um-reviews-reply-edit">
			<a href="javascript:void(0);">
				<i class="um-faicon-pencil"></i> <span><?php _e( 'Edit', 'um-reviews' ); ?></span>
			</a>
		</div>
		<div class="um-reviews-reply-remove">
			<a href="javascript:void(0);" data-reply_id="<?php echo esc_attr( $r->comment_ID ); ?>" data-remove="<?php esc_attr_e( 'Are you sure you want to remove this reply?', 'um-reviews' ); ?>">
				<i class="um-faicon-trash"></i> <span><?php _e( 'Remove', 'um-reviews' ); ?></span>
			</a>
		</div>
	</div>

<?php }