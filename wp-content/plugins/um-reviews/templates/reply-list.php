<?php
/**
 * Template for the UM User Reviews, The "Add review" block
 *
 * Page: "Profile", tab "Reviews"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/reply-list.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


um_fetch_user( $r->user_id ); ?>


<div class="um-reviews-reply-item">

	<div class="um-reviews-reply-img">
		<a href="<?php echo esc_url( um_user_profile_url( $r->user_id ) ); ?>">
			<?php echo um_user( 'profile_photo',40 ); ?>
		</a>
	</div>

	<div class="um-reviews-reply-post review-reply-list">

		<span class="um-reviews-reply-meta">
			<?php printf( __( 'by <a href="%s">%s</a>, %s', 'um-reviews' ), um_user_profile_url(), um_user( 'display_name' ), date( UM()->options()->get( 'review_date_format' ), strtotime( $r->comment_date ) ) ); ?>
		</span>

		<span class="um-reviews-reply-content">
			<p><?php echo esc_html( $r->comment_content ); ?></p>
		</span>


		<!--front actions-->
		<?php
			$args = compact( 'review_id', 'reviewer_id', 'profile_id', 'r' );
			UM()->get_template( 'reply-front-actions.php', um_reviews_plugin, $args, true );
		?>

	</div>

	<!--edit form-->
	<?php
		$args = compact( 'review_id','reviewer_id','profile_id','r');
		UM()->get_template( 'reply-edit.php', um_reviews_plugin, $args, true );
	?>
	<div class="um-clear"></div>
</div>