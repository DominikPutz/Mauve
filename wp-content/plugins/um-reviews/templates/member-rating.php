<?php
/**
 * Template for the UM User Reviews, The Rating block.
 *
 * Page: "Members"
 * Hook: 'um_members_after_user_name'
 * Caller: um_reviews_add_rating() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/member-rating.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rating = UM()->Reviews()->api()->get_rating( $user_id );
?>

<div class="um-member-rating">
	<span class="um-reviews-avg" data-number="5" data-score="<?php echo esc_attr( $rating ); ?>"></span>
</div>