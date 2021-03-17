<?php
/**
 * Template for the UM User Photos, The single "Album" block
 *
 * Page: "Profile", tab "Photos"
 * Parent template: gallery.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-photos/album-block.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>


<div class="um-user-photos-comments-loop">
	<?php
		$comments = get_comments([
			'post_id' => $image_id,
			'type'	  => 'um-user-photos',
			'orderby' => 'comment_ID',
			'order'	  => 'DESC',
			'parent' => 0
		]);
	if ( ! empty( $comments ) ) {
		foreach ( $comments as $comment ) {

			UM()->get_template( 'comment.php', um_user_photos_plugin, array(
				'user_id'     => $comment->user_id,
				'content'     => $comment->comment_content,
				'date'        => $comment->comment_date,
				'id'          => $comment->comment_ID,
				'image_id'    => $image_id,
				'comment'	  => $comment,
			), true );
		}
	} ?>
</div>