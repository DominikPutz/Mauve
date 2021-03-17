<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

um_fetch_user( $user_id );
$avatar  = get_avatar( um_user( 'ID' ),80 );
$likes   = get_comment_meta( $id, '_likes', true );

if(! $likes){
	$likes = [];
}

$has_liked = false;
if( in_array( um_user('ID'),$likes ) ){
	$has_liked = true;
}
?>
<div class="um-user-photos-commentwrap" data-comment_id="<?php echo esc_attr( $id ); ?>">
	<div class="um-user-photos-commentl" id="commentid-<?php echo esc_attr( $id ); ?>">

		<a href="javascript:void(0);" class="um-user-photos-comment-hide um-tip-s" original-title="">
		<i class="um-icon-close-round"></i>	
		</a>
			
			<div class="um-user-photos-comment-avatar hidden-0">
				<a href="<?php echo esc_url( um_user_profile_url() ); ?>"><?php echo $avatar; ?></a>
			</div>

			<div class="um-user-photos-comment-hidden hidden-0">	
				<?php _e('Comment hidden. <a href="javascript:void(0);" class="um-link">Show this comment</a>','um-user-photos'); ?>
			</div>

			<div class="um-user-photos-comment-info hidden-0">

				<div class="um-user-photos-comment-data">
					<span class="um-user-photos-comment-author-link">
						<a href="<?php echo esc_url( um_user_profile_url() ); ?>" class="um-link">
							<strong><?php echo esc_html( um_user('display_name') ); ?></strong>
						</a>
					</span> 
				<span class="um-user-photos-comment-text"><?php echo esc_html( $content ); ?></span>
					
				</div>

				<!-- comment meta-->
				<div class="um-user-photos-comment-meta">
					
					
					
					<?php if ( is_user_logged_in() ) { ?>
					
						<?php if( $has_liked ): ?>
					
						<span><a data-id="<?php echo esc_attr( $id ); ?>" href="javascript:void(0);" class="um-link um-user-photos-comment-like active" data-like_text="<?php _e('Like','um-user-photos'); ?>" data-unlike_text="<?php _e('Unlike','um-user-photos'); ?>"><?php _e('Unlike','um-user-photos'); ?></a></span>
					
						<?php else: ?>
					
						<span><a data-id="<?php echo esc_attr( $id ); ?>" href="javascript:void(0);" class="um-link um-user-photos-comment-like" data-like_text="<?php _e('Like','um-user-photos'); ?>" data-unlike_text="<?php _e('Unlike','um-user-photos'); ?>"><?php _e('Like','um-user-photos'); ?></a></span>
						
						<?php endif; ?>
					
					
					
						<span class="um-user-photos-comment-likes count-<?php echo esc_attr( count( $likes ) ); ?>">

							<a href="javascript:void(0);" data-id="<?php echo esc_attr( $id ); ?>" data-modal_title="<?php esc_attr_e('Likes','um-user-photos'); ?>">
								<i class="um-faicon-thumbs-up"></i>

								<ins class="um-user-photos-ajaxdata-commentlikes">
								<?php echo esc_html( count( $likes ) ); ?>
								</ins>
							</a>

						</span>
					
						<?php if ( UM()->Photos_API()->can_comment() ) : ?>
						<!--<span>
							<a href="javascript:void(0);" class="um-link um-user-photos-comment-reply" data-commentid="<?php echo esc_attr( $id ); ?>"><?php _e('Reply','um-user-photos'); ?></a>
						</span>-->
						<?php endif; ?>
					
				<?php }  // is_user_logged_in() ?>
					
					
					<span>
						<a href="javascript:void(0);" class="um-user-photos-comment-permalink">
							<?php echo human_time_diff( strtotime( $date ),time() ).__(' ago','um-user-photos'); ?>
						</a>
					</span>

					<?php if ( is_user_logged_in() &&  UM()->Photos_API()->can_edit_comment( $id , get_current_user_id()) ) { ?>
					<span class="um-user-photos-editc"><a href="javascript:void(0);"><i class="um-icon-edit"></i></a>
						<span class="um-user-photos-editc-d">
							
							<?php if ( UM()->Photos_API()->is_comment_author( $id , get_current_user_id() ) ): ?>
							<a href="javascript:void(0);"
							   class="edit"
							   data-modal_title="<?php _e('Edit comment','um-user-photos'); ?>"
							   data-template="modal/edit-comment"
							   data-commentid="<?php echo esc_attr( $id ); ?>">
							   	<?php esc_html_e('Edit','um-user-photos'); ?>
							   </a>
							<?php endif; ?>
							
							<a href="javascript:void(0);" class="delete" data-modal_title="<?php _e('Delete comment','um-user-photos'); ?>" data-commentid="<?php echo esc_attr( $id ); ?>" data-msg="<?php _e('Are you sure you want to delete this comment?','um-user-photos'); ?>"><?php _e('Delete','um-user-photos'); ?></a>
							
						</span>
					</span>
					<?php } ?>
					
				</div>
				<!-- comment meta-->
				
				

			</div>

		</div>
	</div>