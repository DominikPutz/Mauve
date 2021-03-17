<?php
/**
 * Template for the UM User Reviews, The "Add review" block
 *
 * Page: "Profile", tab "Reviews"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/reply-list.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( is_user_logged_in() && get_current_user_id() == $profile_id ) { ?>

	<div class="um-reviews-reply-post review-reply-form review-reply-edit-form">

		<a href="javascript:void(0);" class="um-reviews-reply-cancel-edit"><i class="um-icon-close"></i></a>

		<form class="um-reviews-reply-form" action="<?= admin_url('admin-ajax.php?action=um_reviews_reply'); ?>" method="post">

			<span class="um-reviews-reply-meta">
				<?php printf( __( 'by <a href="%s">%s</a>, %s', 'um-reviews' ), um_user_profile_url(), um_user( 'display_name' ), date( UM()->options()->get( 'review_date_format' ), strtotime( $r->comment_date ) ) ); ?>
			</span>

			<span class="um-reviews-reply-content">
				<textarea name="content" placeholder="<?php esc_attr_e( 'Enter your reply...', 'um-reviews' ); ?>"><?php echo esc_textarea( $r->comment_content ); ?></textarea>
			</span>

			<div class="um-field-error" style="display:none"></div>

			<span class="um-reviews-reply-send">
				<input type="submit" value="<?php esc_attr_e('Save Reply', 'um-reviews' ); ?>" class="um-button" />
			</span>

			<input type="hidden" name="review_id" value="<?php echo esc_attr( $review_id ); ?>"/>
			<input type="hidden" name="reply_id" value="<?php echo esc_attr( $r->comment_ID ); ?>"/>
			<input type="hidden" name="profile_id" value="<?php echo esc_attr( um_profile_id() ); ?>"/>
			<?php wp_nonce_field( 'um_reviews_reply_update_' . $r->comment_ID ); ?>

		</form>

	</div>

<?php }