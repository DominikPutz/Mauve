<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Edit Post JS Template -->
<script type="text/template" id="tmpl-um-edit-post">
	<form action="" method="post" class="um-activity-publish">
		<input type="hidden" name="action" value="um_activity_publish" />
		<input type="hidden" name="_post_id" value="{{{data.post_id}}}" />
		<input type="hidden" name="_wall_id" value="<?php echo esc_attr( $user_id ); ?>" />
		<input type="hidden" name="_post_img" value="{{{data._photo}}}" />
		<input type="hidden" name="_post_img_url" value="{{{data._photo_url}}}" />
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'um-frontend-nonce' ) ?>" />

		<div class="um-activity-body">

			<div class="um-activity-textarea">
				<textarea data-photoph="<?php esc_attr_e( 'Say something about this photo', 'um-activity' ); ?>"
				          data-ph="<?php esc_attr_e( 'What\'s on your mind?','um-activity' ); ?>"
				          placeholder="<?php esc_attr_e( 'What\'s on your mind?','um-activity' ); ?>"
				          class="um-activity-textarea-elem" name="_post_content">{{{data.textarea}}}</textarea>
			</div>

			<div class="um-activity-preview">
				<span class="um-activity-preview-spn">
					<img src="{{{data._photo_url}}}" alt="" title="" width="" height="" />
					<span class="um-activity-img-remove">
						<i class="um-icon-close"></i>
					</span>
				</span>
			</div>

			<div class="um-clear"></div>
		</div>

		<div class="um-activity-foot">

			<div class="um-activity-left um-activity-insert">

				<?php do_action( 'um_activity_pre_insert_tools' );

				if ( ! UM()->roles()->um_user_can( 'activity_photo_off' ) ) {
					$timestamp = current_time( "timestamp" );
					$nonce = wp_create_nonce( 'um_upload_nonce-' . $timestamp ); ?>
					<a href="javascript:void(0);" class="um-activity-insert-photo um-tip-s" data-timestamp="<?php echo esc_attr( $timestamp );?>" data-nonce="<?php echo esc_attr( $nonce );?>"
					   title="<?php esc_attr_e( 'Add photo', 'um-activity' ); ?>" data-allowed="gif,png,jpeg,jpg"
					   data-size-err="<?php esc_attr_e( 'Image is too large', 'um-activity' ); ?>"
					   data-ext-err="<?php esc_attr_e( 'Please upload a valid image', 'um-activity' ); ?>">
						<i class="um-faicon-camera"></i>
					</a>
				<?php }

				do_action( 'um_activity_post_insert_tools' ); ?>

				<div class="um-clear"></div>
			</div>

			<div class="um-activity-right">
				<a href="javascript:void(0);" class="um-activity-edit-cancel">
					<?php _e( 'Cancel editing', 'um-activity' ); ?>
				</a>
				<a href="javascript:void(0);" class="um-button um-activity-post um-disabled">
					<?php _e( 'Update', 'um-activity' ); ?>
				</a>
			</div>
			<div class="um-clear"></div>

		</div>
	</form>
</script>