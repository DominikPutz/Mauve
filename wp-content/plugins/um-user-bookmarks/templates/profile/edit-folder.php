<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<header style="width:100%;display:block;position:relative;">
	<a href="javascript:void(0);" class="um-user-bookmarks-folder-back" data-folder_key="<?php echo esc_attr( $key ); ?>"
	   data-profile="<?php echo esc_attr( $user ) ?>" style="width:5%;float:left;display:inline-block;text-align:center;"
	   data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>">
		<i class="um-faicon-arrow-left"></i>
	</a>

	<h3 style="width:89%;float:none;text-align:center;margin:0;display:inline-block;">
		<?php _e( 'Edit folder', 'um-user-bookmarks' ); ?>
	</h3>
	<div class="um-clear"></div>
</header>

<br/>
<hr/>
<br/>

<form method="post" class="um-user-bookmarks-edit-folder-form">
	<p>
		<input type="text" class="um-form-field" name="folder_title"
		       placeholder="<?php echo esc_attr( sprintf( __( '%s title', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) ); ?>"
		       value="<?php echo esc_attr( $folder['title'] ); ?>">
		<small class="error-message"><?php printf( __( '%s title is required', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ); ?></small>
	</p>

	<p>
		<input id="um_user_bookmarks_access_type_checkbox" name="is_private" type="checkbox" value="1" <?php checked( $private ) ?> />
		<label for="um_user_bookmarks_access_type_checkbox"><?php _e('Private','um-user-bookmarks' ); ?></label>
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

<div class="um-clear"></div>