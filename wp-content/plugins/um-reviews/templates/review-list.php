<?php
/**
 * Template for the UM User Reviews, The list of reviews
 *
 * Page: "Profile", tab "Reviews"
 * Caller: um_profile_content_reviews_default() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/review-list.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( UM()->Reviews()->api()->already_reviewed( um_profile_id() ) ) {
	$my_id = get_current_user_id();
} else {
	$my_id = null;
}

foreach ( $reviews as $review ) {
	setup_postdata( $review );
	$content = wp_strip_all_tags( $review->post_content );

	$reviewer_id = get_post_meta( $review->ID, '_reviewer_id', true );
	$reviewer = get_userdata( $reviewer_id );
	um_fetch_user( $reviewer_id );
	?>

	<div class="um-reviews-item" id="review-<?php echo esc_attr( $review->ID ); ?>" data-review_id="<?php echo esc_attr( $review->ID ); ?>" data-user_id="<?php echo esc_attr( um_profile_id() ); ?>">

		<div class="um-reviews-img">
			<a href="<?php echo esc_url( um_user_profile_url( $reviewer_id ) ); ?>"><?php echo um_user( 'profile_photo',40 ); ?></a>
		</div>

		<div class="um-reviews-post review-list">
			<span class="um-reviews-avg" data-number="5" data-score="<?php echo esc_attr( get_post_meta( $review->ID, '_rating', true ) ); ?>"></span>
			<span class="um-reviews-title"><span><?php echo get_the_title( $review ); ?></span></span>
			<span class="um-reviews-meta"><?php printf( __('by <a href="%s">%s</a>, %s','um-reviews' ), um_user_profile_url(), um_user( 'display_name' ), get_the_time( UM()->options()->get( 'review_date_format' ), $review ) ); ?></span>
			<span class="um-reviews-content"><?php echo nl2br( $content ); ?></span>
			<?php if ( UM()->Reviews()->api()->is_flagged( $review->ID ) ) { ?>
				<div class="um-reviews-flagged"><?php esc_html_e( 'This is currently being reviewed by an admin', 'um-reviews' ); ?></div>
			<?php } ?>
			<div class="um-reviews-note"></div>
			<div class="um-reviews-tools"><?php do_action('um_review_front_actions', um_profile_id(), $reviewer_id, $my_id, $review->ID ); ?></div>
		</div>

		<div class="um-reviews-post review-form">

			<a href="javascript:void(0);" class="um-reviews-cancel-edit"><i class="um-icon-close"></i></a>

			<form class="um-reviews-form" action="" method="post">

				<span class="um-reviews-rate" data-key="rating" data-number="5" data-score="<?php echo esc_attr( get_post_meta( $review->ID, '_rating', true ) ); ?>"></span>

				<span class="um-reviews-title"><input type="text" name="title" placeholder="<?php esc_attr_e('Enter subject...','um-reviews'); ?>" value="<?php echo esc_attr( $review->post_title ); ?>" /></span>

				<span class="um-reviews-meta"><?php printf( __( 'by <a href="%s">%s</a>, %s','um-reviews' ), um_user_profile_url(), um_user( 'display_name' ), current_time( UM()->options()->get( 'review_date_format' ) ) ); ?></span>

				<span class="um-reviews-content">
					<textarea name="content" placeholder="<?php esc_attr_e('Enter your review...','um-reviews'); ?>"><?php echo esc_textarea( isset( $content ) ? $content: '' ); ?></textarea>
				</span>

				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( um_profile_id() ); ?>" />
				<input type="hidden" name="reviewer_id" id="reviewer_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
				<input type="hidden" name="action" id="action" value="um_review_edit" />
				<input type="hidden" name="nonce" id="action" value="<?php echo wp_create_nonce( 'um-frontend-nonce' ) ?>" />

				<input type="hidden" name="review_id" id="review_id" value="<?php echo esc_attr( $review->ID ); ?>" />
				<input type="hidden" name="rating_old" id="rating_old" value="<?php echo esc_attr( get_post_meta( $review->ID, '_rating', true ) ); ?>" />
				<input type="hidden" name="reviewer_publish" id="reviewer_publish" value="<?php echo esc_attr( UM()->roles()->um_user_can( 'can_publish_review' ) ); ?>" />

				<div class="um-field-error" style="display:none"></div>

				<span class="um-reviews-send">
					<input type="submit" value="<?php esc_attr_e('Save Review','um-reviews'); ?>" class="um-button" />
				</span>

			</form>

		</div>
		<div class="um-clear"></div>
		
		<?php do_action('um_review_after_review_content', $review->ID, $reviewer_id , um_profile_id() ); ?>
		
		<div class="um-clear"></div>

	</div>

<?php }

um_reset_user();
wp_reset_postdata();
wp_reset_query();
