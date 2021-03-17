<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-user-bookmarked-list">
	<?php foreach ( $bookmarks as $post_id ) {
		$bookmarked_post = get_post( $post_id );
		if ( empty( $bookmarked_post ) || is_wp_error( $bookmarked_post ) ) {
			continue;
		}

		$has_image       = false;
		$has_image_class = 'no-image';
		$image           = '';
		if ( has_post_thumbnail( $post_id ) ) {
			$has_image       = true;
			$has_image_class = 'has-image';
			$image           = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );
		}

		UM()->get_template( 'profile/single-folder/bookmark-item.php', um_user_bookmarks_plugin, array(
			'has_image_class' => $has_image_class,
			'image'           => $image,
			'post_link'       => get_the_permalink( $post_id ),
			'post_title'      => get_the_title( $post_id ),
			'has_image'       => $has_image,
			'excerpt'         => substr( strip_tags( $bookmarked_post->post_content ), 0, 130 ),
			'id'              => $post_id,
		), true );
	} ?>
	<div class="um-clear"></div>
</div>

<div class="um-clear"></div>