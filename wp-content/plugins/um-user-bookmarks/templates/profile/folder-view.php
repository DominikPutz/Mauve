<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( $user_bookmarks ) {

	if ( ! $profile_id ) {
		$profile_id = um_profile_id();
	}

	foreach ( $user_bookmarks as $key => $value ) {
		if ( ! $include_private && $value['type'] == 'private' ) {
			continue;
		}

		$count = 0;
		if ( isset( $value['bookmarks'] ) && count( $value['bookmarks'] ) ) {
			$count = count( $value['bookmarks'] );
		}

		UM()->get_template( 'profile/folder-view/folder.php', um_user_bookmarks_plugin, array(
			'profile_id'    => $profile_id,
			'key'           => $key,
			'folder'        => $value,
			'count'         => $count,
		), true );
	} ?>

	<div class="um-clear"></div>

<?php } else {
	_e( 'No bookmarks have been added.', 'um-user-bookmarks' );
}

if ( is_user_logged_in() && get_current_user_id() == um_profile_id() ) {
	UM()->get_template( 'profile/folder-view/add-folder.php', um_user_bookmarks_plugin, array(
		'folder_text'   => UM()->User_Bookmarks()->get_folder_text(),
	), true );
}