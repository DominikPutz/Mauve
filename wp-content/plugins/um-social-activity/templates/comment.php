<?php foreach ( $comments as $comment ) {
	um_fetch_user( $comment->user_id );

	$avatar      = get_avatar( um_user( 'ID' ),80 );

	$likes       = get_comment_meta( $comment->comment_ID, '_likes', true );
	$user_hidden = UM()->Activity_API()->api()->user_hidden_comment( $comment->comment_ID ); ?>

	<div class="um-activity-commentwrap" data-comment_id="<?php echo esc_attr( $comment->comment_ID ); ?>">

		<div class="um-activity-commentl" id="commentid-<?php echo esc_attr( $comment->comment_ID ); ?>">

			<?php if ( is_user_logged_in() && ! $user_hidden ) { ?>
				<a href="javascript:void(0);" class="um-activity-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>
			<?php } ?>

			<div class="um-activity-comment-avatar hidden-<?php echo esc_attr( $user_hidden ); ?>"><a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo $avatar; ?></a></div>

			<div class="um-activity-comment-hidden hidden-<?php echo esc_attr( $user_hidden ); ?>"><?php _e('Comment hidden. <a href="javascript:void(0);" class="um-link">Show this comment</a>','um-activity'); ?></div>

			<div class="um-activity-comment-info hidden-<?php echo esc_attr( $user_hidden ); ?>">

				<div class="um-activity-comment-data">
					<span class="um-activity-comment-author-link"><a href="<?php echo um_user_profile_url(); ?>" class="um-link"><?php
							$um_activity_comment_text = UM()->Activity_API()->api()->commentcontent( $comment->comment_content );
							echo esc_html( um_user( 'display_name' ) ); ?></a></span> <span class="um-activity-comment-text"><?php echo str_replace("\'", "'", $um_activity_comment_text ); ?>
					</span>
					<textarea id="um-activity-reply-<?php echo esc_attr( $comment->comment_ID ); ?>" class="original-content" style="display:none!important"><?php echo $comment->comment_content; ?></textarea>
				</div>

				<div class="um-activity-comment-meta">
					<?php if ( is_user_logged_in() ) {

						if ( UM()->Activity_API()->api()->user_liked_comment( $comment->comment_ID ) ) { ?>
							<span><a href="javascript:void(0);" class="um-link um-activity-comment-like active" data-like_text="<?php esc_attr_e('Like','um-activity'); ?>" data-unlike_text="<?php esc_attr_e('Unlike','um-activity'); ?>"><?php _e('Unlike','um-activity'); ?></a></span>
						<?php } else { ?>
							<span><a href="javascript:void(0);" class="um-link um-activity-comment-like" data-like_text="<?php esc_attr_e('Like','um-activity'); ?>" data-unlike_text="<?php esc_attr_e('Unlike','um-activity'); ?>"><?php _e('Like','um-activity'); ?></a></span>
						<?php } ?>

						<span class="um-activity-comment-likes count-<?php echo (int) $likes; ?>"><a href="javascript:void(0);"><i class="um-faicon-thumbs-up"></i><ins class="um-activity-ajaxdata-commentlikes"><?php echo (int) $likes; ?></ins></a></span>

						<?php if ( UM()->Activity_API()->api()->can_comment() ) { ?>
							<span><a href="javascript:void(0);" class="um-link um-activity-comment-reply" data-commentid="<?php echo esc_attr( $comment->comment_ID ); ?>"><?php _e( 'Reply', 'um-activity' ); ?></a></span>
						<?php }

					} ?>

					<span>
						<a href="<?php echo esc_url( UM()->Activity_API()->api()->get_comment_link( $post_link, $comment->comment_ID ) ); ?>" class="um-activity-comment-permalink">
							<?php echo UM()->Activity_API()->api()->get_comment_time( $comment->comment_date ); ?>
						</a>
					</span>

					<?php if ( UM()->Activity_API()->api()->can_edit_comment( $comment->comment_ID, get_current_user_id() ) ) { ?>
						<span class="um-activity-editc"><a href="javascript:void(0);"><i class="um-icon-edit"></i></a>
							<span class="um-activity-editc-d">
								<a href="javascript:void(0);" class="edit" data-commentid="<?php echo esc_attr( $comment->comment_ID ); ?>"><?php _e('Edit','um-activity'); ?></a>
								<a href="javascript:void(0);" class="delete" data-msg="<?php esc_attr_e('Are you sure you want to delete this comment?','um-activity'); ?>"><?php _e('Delete','um-activity'); ?></a>
							</span>
						</span>
					<?php } ?>

				</div>

			</div>

		</div>

		<?php $comm_num = ( isset( $_GET['wall_comment_id'] ) && absint( $_GET['wall_comment_id'] ) ) ? 10000 : UM()->options()->get( 'activity_init_comments_count' );

		$child = get_comments( array(
			'post_id'   => $post_id,
			'parent'    => $comment->comment_ID,
			'number'    => $comm_num,
			'offset'    => 0,
			'order'     => UM()->options()->get( 'activity_order_comment' )
		) );

		$child_all = UM()->Activity_API()->api()->get_replies_number( $post_id, $comment->comment_ID ); ?>

		<div class="um-activity-comment-child">

			<?php
			foreach ( $child as $commentc ) {
				um_fetch_user( $commentc->user_id );

				UM()->Activity_API()->shortcode()->args = $t_args = array(
					'commentc'  => $commentc,
					'post_id'   => $post_id,
					'post_link' => UM()->Activity_API()->api()->get_permalink( $post_id ),
				);
				UM()->get_template( 'comment-reply.php', um_activity_plugin, $t_args, true );
			}

			// Do we have more comments
			if ( $child_all > count( $child ) ) {
				$calc = $child_all - count( $child );
				if ( $calc > 1 ) {
					$text = sprintf(__('load <span class="um-activity-more-count">%s</span> more replies','um-activity'), $calc );
				} else if ( $calc == 1 ) {
					$text = sprintf(__('load <span class="um-activity-more-count">%s</span> more reply','um-activity'), $calc );
				}
				echo '<a href="javascript:void(0);" class="um-activity-ccommentload" data-load_replies="' . esc_attr__( 'load more replies', 'um-activity' ) . '" data-load_comments="' . esc_attr__('load more comments','um-activity') . '" data-loaded="'. esc_attr( count( $child ) ) . '"><i class="um-icon-forward"></i><span>' . $text . '</span></a>';
				echo '<div class="um-activity-ccommentload-spin"></div>';
			} ?>

		</div>
	</div>
<?php }

// reset um user
um_reset_user();