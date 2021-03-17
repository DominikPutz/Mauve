<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form method="post" action="<?php echo esc_attr( $form_action ); ?>" class="um-user-bookmarks-edit-folder-form">
	<p>
		<input type="text" class="um-form-field" name="folder_title"
		       placeholder="<?php echo esc_attr( sprintf( __( '%s title', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) ); ?>"
		       value="<?php echo esc_attr( $folder_title ); ?>">
		<small class="error-message"><?php printf( __( '%s title is required', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ); ?></small>
	</p>

	<p>
		<input id="um_user_bookmarks_access_type_checkbox" name="is_private" type="checkbox" value="1" <?php echo $checkbox; ?> />
		<label for="um_user_bookmarks_access_type_checkbox"><?php _e( 'Private', 'um-user-bookmarks' ); ?></label>
	</p>

	<p>
		<button type="button" class="um-modal-btn um_user_bookmarks_action_folder_update">
			<?php _e( 'Update', 'um-user-bookmarks' ) ?>
		</button>
	</p>

	<?php wp_nonce_field('um_user_bookmarks_update_folder'); ?>
	<input type="hidden" name="folder_key" value="<?php echo esc_attr( $key ); ?>" />
	<input type="hidden" name="user" value="<?php echo esc_attr( $user ); ?>" />
	<input type="hidden" name="action" value="um_bookmarks_update_folder" />

	<div class="form-response" style="text-align:center;color:#ab1313;"></div>
</form>