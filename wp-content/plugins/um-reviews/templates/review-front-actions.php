<?php
/**
 * Template for the UM User Reviews, The Review actions.
 *
 * Page: "Profile", tab "Reviews"
 * Hook: 'um_review_front_actions'
 * Caller: um_review_front_actions() function
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/review-front-actions.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( UM()->Reviews()->api()->can_reply( um_profile_id(), $review_id ) ) { ?>

	<div class="um-reviews-reply"><a href="javascript:void(0);"><i class="um-faicon-reply"></i> <span><?php _e( 'Reply', 'um-reviews' ); ?></span></a></div>

<?php }


if ( UM()->Reviews()->api()->can_flag( $review_id ) ) { ?>

	<div class="um-reviews-flag"><a href="javascript:void(0);"><i class="um-faicon-flag"></i> <span><?php _e( 'Report', 'um-reviews' ); ?></span></a></div>

<?php }

if ( $reviewer_id == $my_id && UM()->Reviews()->api()->already_reviewed( $user_id ) ) { ?>

	<div class="um-reviews-edit"><a href="javascript:void(0);"><i class="um-faicon-pencil"></i> <span><?php _e( 'Edit', 'um-reviews' ); ?></span></a></div>

<?php } elseif ( UM()->Reviews()->api()->can_edit( $reviewer_id ) ) { ?>

	<div class="um-reviews-edit"><a href="javascript:void(0);"><i class="um-faicon-pencil"></i> <span><?php _e( 'Edit', 'um-reviews' ); ?></span></a></div>

<?php }

if ( UM()->Reviews()->api()->can_remove( $reviewer_id ) ) { ?>

	<div class="um-reviews-remove"><a href="javascript:void(0);" data-review_id="'. $review_id .'" data-remove="<?php esc_attr_e( 'Are you sure you want to remove this review?', 'um-reviews' ); ?>"><i class="um-faicon-trash"></i> <span><?php _e( 'Remove', 'um-reviews' ); ?></span></a></div>

<?php }
