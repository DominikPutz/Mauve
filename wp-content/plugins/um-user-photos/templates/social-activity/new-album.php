<?php
/**
 * Template for the UM User Photos, the "Activity" post content
 *
 * Page: "Activity"
 * Page: "Profile", tab "Activity"
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/social-activity/new-album.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?><a href="{author_profile}" class="um-link" target="_blank">{author_name}</a> <?php _e('created a new photo album','um-user-photos');?> <a href="{post_url}" class="um-link" target="_blank"><strong>{post_title}</strong></a>.
<span style="padding-bottom:0;" class="post-meta"><div id="um-user-album-{post_image}" class="um_user_photos_activity_view">{post_excerpt}</div></span>