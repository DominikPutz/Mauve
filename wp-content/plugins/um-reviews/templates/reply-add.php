<?php
/**
 * Template for the UM User Reviews, The "Add review" block
 *
 * Page: "Profile", tab "Reviews"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/reply-add.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( UM()->Reviews()->api()->can_reply( $profile_id, $review_id ) ) {

	um_fetch_user( $profile_id ); ?>

	<div class="um-reviews-reply-post review-reply-form review-reply-new-form">

		<a href="javascript:void(0);" class="um-reviews-reply-cancel-edit um-new-review-reply-cancel"><i class="um-icon-close"></i></a>

		<form class="um-reviews-reply-form" action="<?= admin_url( 'admin-ajax.php?action=um_reviews_reply' ); ?>" method="post">

			<span class="um-reviews-reply-meta">
				<?php printf( __('by <a href="%s">%s</a>, %s','um-reviews' ), um_user_profile_url(), um_user( 'display_name' ), date( UM()->options()->get( 'review_date_format' ), time() ) ); ?>
			</span>

			<span class="um-reviews-reply-content">
				<textarea name="content" placeholder="<?php esc_attr_e('Enter your reply...','um-reviews' ); ?>"></textarea>
			</span>

			<div class="um-field-error" style="display:none;"></div>

			<span class="um-reviews-reply-send">
				<input type="submit" value="<?php esc_attr_e( 'Reply', 'um-reviews' ); ?>" class="um-button" />
			</span>

			<input type="hidden" name="review_id" value="<?php echo esc_attr( $review_id ); ?>"/>

			<input type="hidden" name="profile_id" value="<?php echo esc_attr( um_profile_id() ); ?>"/>

			<?php wp_nonce_field( 'um_reviews_reply_send' ); ?>
		</form>

	</div>

<?php }