<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * confirm box for activity
 */
function um_activity_confirm_box() {

	if ( UM()->Activity_API()->shortcode()->confirm_box ) {
		return;
	}

	UM()->Activity_API()->shortcode()->confirm_box = true; ?>

	<div class="um-activity-confirm-o"></div>
	<div class="um-activity-confirm">
		<div class="um-activity-confirm-m">

		</div>
		<div class="um-activity-confirm-b">
			<a href="javascript:void(0);" class="um-activity-confirm-btn"><?php _e( 'Yes', 'um-activity' ); ?></a>
			<a href="javascript:void(0);" class="um-activity-confirm-close"><?php _e( 'No', 'um-activity' ); ?></a>
		</div>
	</div>
		
	<style type="text/css">
		
		.um-activity-commentl.highlighted,
		.um-activity-comment-child .um-activity-commentl.highlighted
		{ border-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;  }
		
		.um-activity-widget.highlighted .um-activity-head {
			border-top-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
			border-left-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
			border-right-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
			border-left-width: 2px;
			border-right-width: 2px;
			border-top-width: 2px;
		}
		
		.um-activity-widget.highlighted .um-activity-body,
		.um-activity-widget.highlighted .um-activity-foot,
		.um-activity-widget.highlighted .um-activity-comments {
			border-left-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
			border-right-color: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
			border-left-width: 2px;
			border-right-width: 2px;
		}
		
		.um-activity-widget.highlighted .um-activity-comments {
			border-bottom: 2px solid <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>;
		}
		
		.um-activity-dialog a:hover {background: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?>}
		ul.ui-autocomplete li.ui-menu-item:hover {background: <?php echo esc_attr( UM()->options()->get( 'activity_highlight_color' ) ); ?> !important}
		
	</style>
		
	<?php
}