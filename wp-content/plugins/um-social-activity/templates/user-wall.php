<?php
//build posts query
$args = array(
	'post_type'     => 'um_activity',
	'post_status'   => array( 'publish' ),
);

if ( isset( $wall_post ) && $wall_post > 0 ) {

	$args['post__in'] = array( $wall_post );

	$followed_ids = UM()->Activity_API()->api()->followed_ids();
	if ( $followed_ids ) {
		$args['meta_query'][] = array(
			'key'       => '_user_id',
			'value'     => $followed_ids,
			'compare'   => 'IN'
		);
	}

	$friends_ids = UM()->Activity_API()->api()->friends_ids();
	if ( $friends_ids ) {
		$args['meta_query'][] = array(
			'key'       => '_user_id',
			'value'     => $friends_ids,
			'compare'   => 'IN'
		);
	}

} else {

	//set offset when pagination
	$args['posts_per_page'] = UM()->Activity_API()->api()->get_posts_per_page();
	if ( isset( $offset ) ) {
		$args['offset'] = $offset;
	}

	//If $user_wall == 0 - Loads Global Site Activity
	//If $user_wall == 1 - Loads User Wall and $user_id -
	if ( ! empty( $user_wall ) ) {
		if ( ! empty( $user_id ) ) {
			$args['meta_query'][] = array(
				'relation'	=> 'OR',
				array(
					'key'       => '_wall_id',
					'value'     => $user_id,
					'compare'   => '='
				),
				array(
					'key'       => '_user_id',
					'value'     => $user_id,
					'compare'   => '='
				)
			);
		}
	} else {
		$followed_ids = UM()->Activity_API()->api()->followed_ids();
		if ( $followed_ids ) {
			$args['meta_query'][] = array(
				'key'       => '_user_id',
				'value'     => $followed_ids,
				'compare'   => 'IN'
			);
		}

		$friends_ids = UM()->Activity_API()->api()->friends_ids();
		if ( $friends_ids ) {
			$args['meta_query'][] = array(
				'key'       => '_user_id',
				'value'     => $friends_ids,
				'compare'   => 'IN'
			);
		}

		/*if ( ! $friends_ids && ! $followed_ids && ! empty( $user_id ) ) {
			$args['meta_query'][] = array(
				'relation'	=> 'OR',
				array(
					'key'       => '_wall_id',
					'value'     => $user_id,
					'compare'   => '='
				),
				array(
					'key'       => '_user_id',
					'value'     => $user_id,
					'compare'   => '='
				),
				array(
					'key'       => '_wall_id',
					'value'     => 0,
					'compare'   => '='
				)
			);
		}*/
	}


	if ( isset( $hashtag ) && $hashtag ) {

		$hashtag = str_replace( '#', '', $hashtag );
		$args['tax_query'] = array(
			array(
				'taxonomy'  => 'um_hashtag',
				'field'     => 'slug',
				'terms'     => array ( $hashtag )
			)
		);

	}
}
/*******************************************************************/
$args = apply_filters( 'um_activity_wall_args', $args );

$wallposts = new WP_Query( $args );

if ( $wallposts->found_posts == 0 ) {
	return;
}

foreach ( $wallposts->posts as $post ) {
	$author_id = UM()->Activity_API()->api()->get_author( $post->ID );
	$can_view = UM()->Activity_API()->api()->can_view_wall( $author_id );
	// exclude private walls
	if ( $can_view !== true ) {
		continue;
	}

	$wall_id = UM()->Activity_API()->api()->get_wall( $post->ID );
	$post_link = UM()->Activity_API()->api()->get_permalink( $post->ID );
	um_fetch_user( $author_id ); ?>

	<div class="um-activity-widget" id="postid-<?php echo esc_attr( $post->ID ); ?>">

		<div class="um-activity-head">

			<div class="um-activity-left um-activity-author">
				<div class="um-activity-ava">
					<a href="<?php echo esc_url( um_user_profile_url() ); ?>">
						<?php echo get_avatar( $author_id, 80 ); ?>
					</a>
				</div>
				<div class="um-activity-author-meta">
					<div class="um-activity-author-url">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
							<?php echo um_user('display_name', 'html'); ?>
						</a>
						<?php if ( $wall_id && $wall_id != $author_id ) {
							um_fetch_user( $wall_id ); ?>
							<i class="um-icon-forward"></i>
							<a href="<?php echo esc_url( um_user_profile_url() ) ?>" class="um-link">
								<?php echo esc_html( um_user( 'display_name' ) ) ?>
							</a>
						<?php } ?>
					</div>
					<span class="um-activity-metadata">
						<a href="<?php echo esc_url( $post_link ); ?>">
							<?php echo UM()->Activity_API()->api()->get_post_time( $post->ID ); ?>
						</a>
					</span>
				</div>
			</div>

			<div class="um-activity-right">
				<?php if ( is_user_logged_in() ) { ?>

					<a href="javasscript:void(0);" class="um-activity-ticon um-activity-start-dialog" data-role="um-activity-tool-dialog">
						<i class="um-faicon-chevron-down"></i>
					</a>

					<div class="um-activity-dialog um-activity-tool-dialog">

						<?php if ( ( current_user_can('edit_users') || $author_id == get_current_user_id() ) && ( UM()->Activity_API()->api()->get_action_type( $post->ID ) == 'status' ) ) { ?>
							<a href="javascript:void(0);" class="um-activity-manage">
								<?php _e('Edit','um-activity'); ?>
							</a>
						<?php }

						if ( current_user_can('edit_users') || $author_id == get_current_user_id() ) { ?>
							<a href="javascript:void(0);" class="um-activity-trash"
							   data-msg="<?php esc_attr_e('Are you sure you want to delete this post?','um-activity'); ?>">
								<?php _e('Delete','um-activity'); ?>
							</a>
						<?php }

						if ( $author_id != get_current_user_id() ) { ?>
							<span class="sep"></span>
							<a href="javascript:void(0);" class="um-activity-report <?php if ( UM()->Activity_API()->api()->reported( $post->ID ) ) echo 'flagged'; ?>"
							   data-report="<?php esc_attr_e('Report','um-activity'); ?>"
							   data-cancel_report="<?php esc_attr_e('Cancel report','um-activity'); ?>">
								<?php echo ( UM()->Activity_API()->api()->reported( $post->ID, get_current_user_id() ) ) ? __( 'Cancel report', 'um-activity' ) : __( 'Report', 'um-activity' ); ?>
							</a>
						<?php } ?>

					</div>

				<?php } ?>
			</div>

			<div class="um-clear"></div>
		</div>

		<?php $has_video = UM()->Activity_API()->api()->get_video( $post->ID );
		$has_text_video = get_post_meta( $post->ID , '_video_url', true );
		$has_oembed = get_post_meta( $post->ID , '_oembed', true ); ?>

		<div class="um-activity-body">
			<div class="um-activity-bodyinner<?php if( $has_video || $has_text_video ){ echo ' has-embeded-video'; } ?> <?php if( $has_oembed ){ echo ' has-oembeded'; } ?>">
				<div class="um-activity-bodyinner-edit">
					<textarea style="display: none;"><?php echo esc_attr( get_post_meta( $post->ID, '_original_content', true ) ); ?></textarea>

					<?php $photo_base = get_post_meta( $post->ID, '_photo', true );
					$photo_url = UM()->Activity_API()->api()->get_download_link( $post->ID, $author_id ); ?>
					<input type="hidden" name="_photo" value="<?php echo esc_attr( $photo_base ); ?>" />
					<input type="hidden" name="_photo_url" value="<?php echo esc_attr( $photo_url ); ?>" />
				</div>

				<?php $um_activity_post = UM()->Activity_API()->api()->get_content( $post->ID, $has_video );
				$um_shared_link = get_post_meta( $post->ID, '_shared_link', true );
				$content = $um_activity_post . $um_shared_link;
				if ( $content ) { ?>
					<div class="um-activity-bodyinner-txt"><?php echo $content; ?></div>
				<?php } ?>

				<div class="um-activity-bodyinner-photo">
					<?php echo UM()->Activity_API()->api()->get_photo( $post->ID, '', $author_id ); ?>
				</div>

				<?php if ( empty( $um_shared_link ) ) { ?>
					<div class="um-activity-bodyinner-video">
						<?php echo $has_video; ?>
					</div>
				<?php } ?>
			</div>

			<?php $likes = UM()->Activity_API()->api()->get_likes_number( $post->ID );
			$comments = UM()->Activity_API()->api()->get_comments_number( $post->ID );

			if ( $likes > 0 || $comments > 0 ) { ?>
				<div class="um-activity-disp">
					<div class="um-activity-left">
						<div class="um-activity-disp-likes">
							<a href="javascript:void(0);" class="um-activity-show-likes um-link" data-post_id="<?php echo esc_attr( $post->ID ); ?>">
								<span class="um-activity-post-likes"><?php echo $likes; ?></span>
								<span class="um-activity-disp-span"><?php _e('likes','um-activity'); ?></span>
							</a>
						</div>
						<div class="um-activity-disp-comments">
							<a href="javascript:void(0);" class="um-link">
								<span class="um-activity-post-comments"><?php echo $comments; ?></span>
								<span class="um-activity-disp-span"><?php _e('comments','um-activity'); ?></span>
							</a>
						</div>
					</div>
					<div class="um-activity-faces um-activity-right">
						<?php echo UM()->Activity_API()->api()->get_faces( $post->ID ); ?>
					</div>
					<div class="um-clear"></div>
				</div>
				<div class="um-clear"></div>
			<?php } ?>

		</div>

		<div class="um-activity-foot status" id="wallcomments-<?php echo $post->ID; ?>">
			<?php if ( is_user_logged_in() ) { ?>

				<div class="um-activity-left um-activity-actions">
					<?php if ( UM()->Activity_API()->api()->user_liked( $post->ID ) ) { ?>
						<div class="um-activity-like active" data-like_text="<?php esc_attr_e('Like','um-activity'); ?>" data-unlike_text="<?php esc_attr_e('Unlike','um-activity'); ?>"><a href="javascript:void(0);"><i class="um-faicon-thumbs-up um-active-color"></i><span class=""><?php _e('Unlike','um-activity'); ?></span></a></div>
					<?php } else { ?>
						<div class="um-activity-like" data-like_text="<?php esc_attr_e('Like','um-activity'); ?>" data-unlike_text="<?php esc_attr_e('Unlike','um-activity'); ?>"><a href="javascript:void(0);"><i class="um-faicon-thumbs-up"></i><span class=""><?php _e('Like','um-activity'); ?></span></a></div>
					<?php }

					if ( UM()->Activity_API()->api()->can_comment() ) { ?>
						<div class="um-activity-comment"><a href="javascript:void(0);"><i class="um-faicon-comment"></i><span class=""><?php _e('Comment','um-activity'); ?></span></a></div>
					<?php } ?>
				</div>

			<?php } else { ?>
				<div class="um-activity-left um-activity-join"><?php echo UM()->Activity_API()->api()->login_to_interact( $post->ID ); ?></div>
			<?php } ?>

			<div class="um-clear"></div>
		</div>

		<?php
		UM()->Activity_API()->shortcode()->args = $t_args = array(
					'post_id' => $post->ID,
			);
		UM()->get_template( 'comments.php', um_activity_plugin, $t_args, true );
		?>

	</div>

<?php }

if ( isset( $args['posts_per_page'] ) && $args['posts_per_page'] > $wallposts->found_posts ) {
	if ( empty( $post_link ) && ob_get_level() ) {
		ob_clean();
	}
	return;
} else {
	if ( ! isset( $wall_post ) ) { ?>
		<div class="um-activity-load"></div>
	<?php } 
}