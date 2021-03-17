<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<a href="javascript:void(0);" class="um-user-bookmarks-folder" data-profile="<?php echo esc_attr( $profile_id ); ?>"
   data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_folder_' . $key ); ?>" data-folder_key="<?php echo esc_attr( $key ); ?>">

	<div class="um-user-bookmarks-folder-container">
		<?php UM()->get_template( 'profile/folder-view/folder/title.php', um_user_bookmarks_plugin, array(
			'title' => $folder['title'],
		), true );

		UM()->get_template( 'profile/folder-view/folder/folder-info.php', um_user_bookmarks_plugin, array(
			'count'         => $count,
			'text'          => __( 'saved', 'um-user-bookmarks' ),
			'access_type'   => ucfirst( $folder['type'] ),
		), true ); ?>
	</div>
</a>