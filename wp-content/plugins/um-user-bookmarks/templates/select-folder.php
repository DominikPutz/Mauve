<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<a href="javascript:void(0);" class="um-user-bookmarks-cancel-btn">&times;</a>

<div class="um-user-bookmarks-modal-heading">
	<?php printf( __( 'Select %s', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ); ?>
</div>

<?php if ( ! empty( $bookmarks ) ) { ?>
	<div>
		<form id="form-um-older-folder-bookmark" class="list-um-user-bookmarks-folder">

			<?php foreach ( $bookmarks as $key => $value ) { ?>

				<label class="um-user-bookmarks-select-folder-label">
					<input class="um_user_bookmarks_old_folder-radio" type="radio" name="_um_user_bookmarks_folder" value="<?php echo esc_attr( $key ); ?>" />
					<i class="access-icon <?php echo ( $value['type'] == 'private' ) ? 'um-faicon-lock' : 'um-faicon-globe'; ?>"></i>
					<?php echo $value['title']; ?>
				</label>

			<?php }

			wp_nonce_field( 'um_user_bookmarks_new_bookmark' ); ?>

			<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
			<input type="hidden" name="action" value="um_bookmarks_add" />
		</form>
	</div>
<?php } ?>

<br />

<form id="form-um-new-folder-bookmark" class="new-um-user-bookmarks-folder">

	<div class="um_bookmarks_table new-um-user-bookmarks-folder-tbl">
		<div class="um_bookmarks_tr">
			<div class="um_bookmarks_td">
				<input type="text" name="_um_user_bookmarks_folder" required
				       placeholder="<?php echo esc_attr( sprintf( __( '%s name', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) ); ?>" />
				<small class="error-message">
					<?php printf( __( '%s name is required.', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) ?>
				</small>
			</div>
			<div class="um_bookmarks_td" width="115px" style="vertical-align: middle;max-width:115px;">
				<input id="um_user_bookmarks_access_type_checkbox" type="checkbox" name="is_private" value="1">
				<label for="um_user_bookmarks_access_type_checkbox"><?php _e('Private','um-user-bookmarks'); ?></label>
			</div>
			<div class="um_bookmarks_td" style="max-width:115px;">
				<button class="um_user_bookmarks_create_folder_btn um-modal-btn" type="button" style="height:50px;">
					<?php _e('Create','um-user-bookmarks'); ?>
				</button>
			</div>
		</div>
	</div>

	<?php wp_nonce_field( 'um_user_bookmarks_new_bookmark' ); ?>
	<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
	<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
	<input type="hidden" name="action" value="um_bookmarks_add" />
	<input type="hidden" name="is_new" value="1" />
	<div class="form-response" style="text-align:center;color:#ab1313;"></div>

</form>