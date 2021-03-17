<?php
/**
 * Template for the UM User Reviews, The "Overview rating" detail block.
 *
 * Page: "Profile", tab "Reviews"
 * Caller: Reviews_Main_API->get_details() method
 * Parent template: review-overview.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/review-detail.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<span class="um-reviews-detail">
	<span class="um-reviews-d-s">
		<a href="<?php echo esc_url( $star_rating_url ); ?>"><?php echo $star_rating_text; ?></a>
	</span>
	<a href="<?php echo esc_url( $star_rating_url ); ?>" class="um-reviews-d-p um-tip-n" title="<?php echo sprintf( __( '%s reviews (%s)', 'um-reviews' ), $count_of_reviews, $progress . '%' ); ?>">
		<span data-width="<?php echo $progress; ?>"></span>
	</a>
	<span class="um-reviews-d-n"><?php echo $count_of_reviews; ?></span>
</span>