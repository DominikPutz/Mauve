<?php
/**
 * Template for the UM User Reviews, The "Overview rating" block
 *
 * Page: "Profile", tab "Reviews"
 * Caller: um_profile_content_reviews_default() function
 * Child template: review-detail.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/review-overview.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-reviews-header">
	<span class="um-reviews-header-span">
		<?php if ( um_is_myprofile() ) {
			_e( 'Your Rating', 'um-reviews' );
		} else {
			_e( 'User Rating', 'um-reviews' );
		} ?>
	</span>
	<span class="um-reviews-avg" data-number="5" data-score="<?php echo esc_attr( UM()->Reviews()->api()->get_rating() ); ?>"></span>
</div>

<div class="um-reviews-avg-rating"><?php echo UM()->Reviews()->api()->avg_rating(); ?></div>

<div class="um-reviews-details">
	<?php UM()->Reviews()->api()->get_details();

	if ( UM()->Reviews()->api()->get_filter() ) { ?>

		<span class="um-reviews-filter"><?php printf( __( '(You are viewing only %s star reviews. <a href="%s">View all reviews</a>)', 'um-reviews' ), UM()->Reviews()->api()->get_filter(), remove_query_arg( 'filter' ) ); ?></span>

	<?php } ?>
</div>