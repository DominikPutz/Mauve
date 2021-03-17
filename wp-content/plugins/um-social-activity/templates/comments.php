<div class="um-activity-comments">

	<?php //hidden comment area for clone
	if ( is_user_logged_in() && UM()->Activity_API()->api()->can_comment() ) { ?>

		<div class="um-activity-commentl um-activity-comment-area">
			<div class="um-activity-comment-avatar">
				<?php echo get_avatar( get_current_user_id(), 80 ); ?>
			</div>
			<div class="um-activity-comment-box">
				<textarea class="um-activity-comment-textarea"
				          data-replytext="<?php esc_attr_e('Write a reply...','um-activity'); ?>"
				          data-reply_to="0"
				          placeholder="<?php esc_attr_e('Write a comment...','um-activity'); ?>"></textarea>
			</div>
			<div class="um-activity-right">
				<a href="javascript:void(0);" class="um-button um-activity-comment-post um-disabled">
					<?php _e( 'Comment', 'um-activity' ); ?>
				</a>
			</div>
			<div class="um-clear"></div>
		</div>

	<?php } ?>

	<div class="um-activity-comments-loop">
		<?php // Comments display
		if ( $post_id > 0 ) {
			$comments_all = UM()->Activity_API()->api()->get_comments_number( $post_id );
			if ( $comments_all > 0 ) {

				$comm_num = ( isset( $_GET['wall_comment_id'] ) && absint( $_GET['wall_comment_id'] ) ) ? 10000 : UM()->options()->get( 'activity_init_comments_count' );

				$comments = get_comments( array(
					'post_id'   => $post_id,
					'parent'    => 0,
					'number'    => $comm_num,
					'offset'    => 0,
					'order'     => UM()->options()->get( 'activity_order_comment' )
				) );

				UM()->Activity_API()->shortcode()->args = $t_args = array(
						'comments' => $comments,
						'post_id' => $post_id,
						'post_link' => UM()->Activity_API()->api()->get_permalink( $post_id )
				);
				UM()->get_template( 'comment.php', um_activity_plugin, $t_args, true );

				// Do we have more comments
				if ( $comments_all > count( $comments ) ) {
					$calc = $comments_all - count( $comments );
					if ( $calc > 1 ) {
						$text = sprintf(__('load <span class="um-activity-more-count">%s</span> more comments','um-activity'), $calc );
					} else if ( $calc == 1 ) {
						$text = sprintf(__('load <span class="um-activity-more-count">%s</span> more comment','um-activity'), $calc );
					} ?>

					<a href="javascript:void(0);" class="um-activity-commentload"
					   data-load_replies="<?php esc_attr_e('load more replies','um-activity') ?>"
					   data-load_comments="<?php esc_attr_e('load more comments','um-activity') ?>"
					   data-loaded="<?php echo esc_attr( count( $comments ) ) ?>">
						<i class="um-icon-forward"></i>
						<span><?php echo $text ?></span>
					</a>
					<div class="um-activity-commentload-spin"></div>

				<?php }
			}
		} ?>

	</div>

</div>
