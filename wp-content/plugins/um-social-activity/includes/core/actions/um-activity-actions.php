<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * New user follow
 *
 * @param $user_id1
 * @param $user_id2
 */
function um_activity_new_follow( $user_id1, $user_id2 ) {
	if ( ! UM()->options()->get('activity-new-follow') ) {
		return;
	}

	um_fetch_user( $user_id2 );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	um_fetch_user( $user_id1 );

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-follow',
			'wall_id'           => 0,
			'author'            => $user_id2,
			'related_id'        => $user_id1,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'user_name'         => um_user('display_name' ),
			'user_profile'      => um_user_profile_url(),
			'user_photo'        => get_avatar( $user_id1, 80 ),
		)
	);
}
add_action( 'um_followers_after_user_follow', 'um_activity_new_follow', 9999, 2 );


/**
 * Undo new follow
 *
 * @param $user_id1
 * @param $user_id2
 */
function um_activity_new_unfollow( $user_id1, $user_id2 ) {
	if ( ! UM()->options()->get( 'activity-new-follow' ) ) {
		return;
	}

	$args = array(
		'post_type' => 'um_activity',
	);

	$args['meta_query'][] = array('key' => '_user_id','value' => $user_id2,'compare' => '=');
	$args['meta_query'][] = array('key' => '_related_id','value' => $user_id1,'compare' => '=');
	$args['meta_query'][] = array('key' => '_action','value' => 'new-follow','compare' => '=');
	$get = new WP_Query( $args );
	if ( $get->found_posts == 0 ) return;
	foreach( $get->posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
add_action( 'um_followers_after_user_unfollow', 'um_activity_new_unfollow', 9999, 2 );


/**
 * New user registration
 *
 * @param $user_id
 */
function um_activity_new_user( $user_id ) {
	if ( ! UM()->options()->get('activity-new-user') ) {
		return;
	}

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-user',
			'wall_id' => 0,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile
		)
	);

}
add_action('um_after_user_is_approved','um_activity_new_user', 90, 1 );


/* new forum topic */
function um_activity_new_topic( $topic_id = 0 ) {
	if ( ! UM()->options()->get('activity-new-topic') ) {
		return;
	}

	$user_id = bbp_get_topic_author_id( $topic_id );

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	if ( bbp_get_topic_content( $topic_id ) ) {
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( bbp_get_topic_content( $topic_id ) ), $num_words = 25, $more = null ) . '</span>';
	} else {
		$post_excerpt = '';
	}

	$post_id = UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-topic',
			'wall_id' => 0,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . bbp_get_topic_title( $topic_id ) . '</span>',
			'post_url' => bbp_get_topic_permalink( $topic_id ),
			'post_excerpt' => $post_excerpt,
		)
	);

	update_user_meta( $user_id,'bbpress_topic_'.$topic_id, $post_id );

}
add_action('bbp_new_topic', 'um_activity_new_topic', 9999, 1 );


/**
 * @param $topic_id
 */
function um_activity_bbp_delete_topic( $topic_id ) {
	global $wpdb;

	$bbpress_topic = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
			'bbpress_topic_' . $topic_id
		) );
		
	wp_delete_post( $bbpress_topic->meta_value );
		
	delete_user_meta( $bbpress_topic->user_id, 'bbpress_topic_'.$topic_id );
}
add_action( 'bbp_delete_topic','um_activity_bbp_delete_topic', 10, 1 );



/* new forum topic */
function um_activity_new_forumwp_topic( $topic_id = 0 ) {
	if ( ! UM()->options()->get( 'activity-new-forumwp-topic' ) ) {
		return;
	}


	$user_id = FMWP()->topic()->get_author_id( $topic_id );


	$topic = get_post( $topic_id );

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	if ( ! empty( $topic->post_content ) ) {
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( $topic->post_content ), $num_words = 25, $more = null ) . '</span>';
	} else {
		$post_excerpt = '';
	}

	$post_id = UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-forumwp-topic',
			'wall_id'           => 0,
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $topic->post_title . '</span>',
			'post_url'          => get_permalink( $topic_id ),
			'post_excerpt'      => $post_excerpt,
		)
	);

	update_user_meta( $user_id,'forumwp_topic_'.$topic_id, $post_id );

}
add_action( 'fmwp_topic_create_completed', 'um_activity_new_forumwp_topic', 9999, 1 );


/**
 * @param $topic_id
 */
function um_activity_forumwp_delete_topic( $topic_id ) {
	global $wpdb;

	$forumwp_topic = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
		'forumwp_topic_' . $topic_id
	) );

	wp_delete_post( $forumwp_topic->meta_value );

	delete_user_meta( $forumwp_topic->user_id, 'forumwp_topic_'.$topic_id );
}
add_action( 'fmwp_after_delete_topic', 'um_activity_forumwp_delete_topic', 9999, 1 );


/* blog post is unpublished */
function um_activity_new_blog_post_undo( $new_status, $old_status, $post ) {
	if ( 'post' !== $post->post_type ) {
		return;
	}

	if ( ! UM()->options()->get('activity-new-post') ) {
		return;
	}

	if ( 'publish' !== $new_status && 'publish' === $old_status ) {
		$args = array(
			'post_type' => 'um_activity',
		);

		$args['meta_query'][] = array('key' => '_related_id','value' => $post->ID,'compare' => '=');
		$args['meta_query'][] = array('key' => '_action','value' => 'new-post','compare' => '=');
		$get = new WP_Query( $args );
		if ( $get->found_posts == 0 ) return;
		foreach( $get->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}
}
add_action( 'transition_post_status', 'um_activity_new_blog_post_undo', 10, 3 );


/* new blog post */
function um_activity_new_blog_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( get_post_type( $post_id ) != 'post' ) {
		return;
	}
	if ( !isset( $_POST['original_post_status'] ) ) {
		return;
	}

	if ( sanitize_key( $_POST['post_status'] ) == 'publish' && sanitize_key( $_POST['original_post_status'] ) == 'publish' ) {
		return;
	}

	if ( ! UM()->options()->get('activity-new-post') ) {
		return;
	}

	$post = get_post( $post_id );
	$user_id = $post->post_author;

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	$post_image = '';
	$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
	if ( $post_image_url ) {
		$post_image_alt = basename( $post_image_url );
		$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
	}

	if ( $post->post_content ) {
		$post_excerpt = strip_shortcodes( $post->post_content );
		$post_excerpt = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $post_excerpt);
		
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( $post_excerpt, $num_words = 25, $more = null ) . '</span>';
	} else {
		$post_excerpt = '';
	}

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-post',
			'wall_id' => $user_id,
			'related_id' => $post_id,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . $post->post_title . '</span>',
			'post_url' => get_permalink( $post_id ),
			'post_excerpt' => $post_excerpt,
			'post_image' => $post_image,
		)
	);

}
add_action('publish_post', 'um_activity_new_blog_post');


/**
 * Updated blog post
 *
 * @param $post_id
 * @param $post_after
 * @param $post_before
 *
 * @return void|int
 */
function um_activity_update_blog_post( $post_id, $post_after, $post_before ) {

	if ( 'post' !== $post_before->post_type ) {
		return;
	}

	$args = array(
		'post_type' => 'um_activity',
	);

	$args['meta_query'][] = array('key' => '_related_id','value' => $post_id,'compare' => '=');
	$args['meta_query'][] = array('key' => '_action','value' => 'new-post','compare' => '=');
	$get = new WP_Query( $args );

	if ( $get->found_posts == 0 ) {
		return;
	}

	foreach ( $get->posts as $post ) {
		$user_id = $post->post_author;

		um_fetch_user( $user_id );
		$author_name = um_user('display_name');
		$author_profile = um_user_profile_url();

		$post_image = '';
		$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
		if ( $post_image_url ) {
			$post_image_alt = basename( $post_image_url );
			$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
		}

		if ( $post_after->post_content ) {
			$post_excerpt = strip_shortcodes( $post_after->post_content );
			$post_excerpt = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $post_excerpt);
			$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( $post_excerpt, $num_words = 25, $more = null ) . '</span>';
		} else {
			$post_excerpt = '';
		}

		$has_updated = UM()->Activity_API()->api()->save(
			array(
				'template' => 'new-post',
				'author' => $user_id,
				'author_name' => $author_name,
				'author_profile' => $author_profile,
				'post_title' => '<span class="post-title">' . $post_after->post_title . '</span>',
				'post_url' => get_permalink( $post_after->ID ),
				'post_excerpt' => $post_excerpt,
				'post_image' => $post_image,
			),
			true,
			$post->ID
		);

	}
}
add_action( 'post_updated', 'um_activity_update_blog_post', 10, 3 );


/* new product */
function um_activity_new_woo_product( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( get_post_type( $post_id ) != 'product' || get_post_status( $post_id ) != 'publish' ) return;

	if ( ! UM()->options()->get('activity-new-product') )
		return;

	$post = get_post($post_id);
	if( $post->post_modified_gmt != $post->post_date_gmt ) return;

	if ( !isset( $_POST['original_post_status'] ) ) return;
	if( sanitize_key( $_POST['post_status'] ) == 'publish' && sanitize_key( $_POST['original_post_status'] ) == 'publish' ) {
		return;
	}

	$product = new WC_Product( $post_id );
	$user_id = $post->post_author;

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	$post_image = '';
	$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
	if ( $post_image_url ) {
		$post_image_alt = basename( $post_image_url );
		$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
	}

	if ( $post->post_excerpt ) {
		$post_excerpt = '<span class="post-excerpt">' . strip_tags( apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ) . '</span>';
	} elseif ( $post->post_content ) {
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( $post->post_content ), $num_words = 25, $more = null ) . '</span>';
	} else  {
		$post_excerpt = '';
	}

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-product',
			'wall_id' => $user_id,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . $post->post_title . '</span>',
			'post_url' => get_permalink( $post_id ),
			'post_excerpt' => $post_excerpt,
			'post_image' => $post_image,
			'price' => '<span class="post-price">' . $product->get_price_html() . '</span>',
		)
	);

}
add_action('save_post', 'um_activity_new_woo_product', 99999, 1 );


/**
 * Remove 'deleted forum topic' from the activties
 *
 * @param int $postid
 */
function um_activity_remove_forum_post( $postid ) {
	global $wpdb;

	if ( function_exists( 'bbp_get_topic_post_type' ) ) {
		$post = get_post( $postid );

		if ( $post && ! is_wp_error( $post ) && bbp_get_topic_post_type() == $post->post_type ) {
			$permalink = get_permalink( $post->ID );

			$activities = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID 
				FROM {$wpdb->posts} 
				WHERE post_status='publish' AND 
					  post_content LIKE %s AND 
					  post_content LIKE %s ",
				'%just created a new forum%',
				"%{$permalink}%"
			) );

			if ( ! empty( $activities ) ) {
				foreach ( $activities as $activityId ) {
					wp_delete_post( $activityId );
				}
			}
		}
	}
}
add_action( 'before_delete_post', 'um_activity_remove_forum_post', 10, 1 );


/**
 * Delete user activities on account deletion
 *
 * @param $user_id
 */
function um_activity_delete_user_activities( $user_id ) {
	$user_activities = get_posts(
		array(
			'post_type'         => 'um_activity',
			'posts_per_page'    => -1,
			'author'            => $user_id
		)
	);

	if ( ! empty( $user_activities ) ) {
		foreach ( $user_activities as $activity ) {
			wp_delete_post( $activity->ID, true );
		}
	}
}
add_action( 'um_delete_user', 'um_activity_delete_user_activities', 10, 1 );


/**
 * Submit form answer
 *
 * @param $entry
 * @param $form
 */
function um_gform_after_submission( $entry, $form ) {

	if ( ! UM()->options()->get( 'activity-new-gform-submission' ) )
		return;

	$post_excerpt = '';
	foreach ( $form['fields'] as $field ) {
		if ( ! empty( $entry[$field->id] ) ) {
			$post_excerpt .=  $field->label . ': ' . $entry[$field->id] . '<br />';
		}
	}

	$user_id = $entry['created_by'];

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-gform-submission',
			'wall_id'           => $user_id,
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $form['title'] . ' ' . __( 'Answer', 'um-activity' ) . '</span>',
			'post_url'          => $entry['source_url'],
			'post_excerpt'      => $post_excerpt,
		)
	);
}
add_action( 'gform_after_submission', 'um_gform_after_submission', 10, 2 );


/**
 * Create new Gravity Form via wp-admin
 *
 * @param $form_meta
 * @param bool $is_new
 */
function um_activity_new_gform( $form_meta, $is_new ) {

	if ( ! UM()->options()->get( 'activity-new-gform' ) ) {
		return;
	}

	if ( ! $is_new ) {
		return;
	}

	$user_id = get_current_user_id();
	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-gform',
			'wall_id'           => $user_id,
			'related_id'        => $form_meta['id'],
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $form_meta['title'] . '</span>',
			'post_url'          => get_permalink( $form_meta['id'] ),
			'post_excerpt'      => $form_meta['description'],
			'post_image'        => '',
		)
	);
}
add_action( 'gform_after_save_form', 'um_activity_new_gform', 10, 2 );


/* add activity */
function um_activity_after_user_verified( $user_id ) {
	if ( ! UM()->options()->get( 'activity-verified-account' ) )
		return;

	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$author_profile = um_user_profile_url();
	$user_photo = get_avatar( $user_id, 24 );

	UM()->Activity_API()->api()->save(
		array(
			'template'       => 'verified-account',
			'wall_id'        => 0,
			'author'         => $user_id,
			'author_name'    => $author_name,
			'author_profile' => $author_profile,
			'user_photo'     => $user_photo,
			'related_id'     => $user_id,
			'custom_path'    => um_verified_users_path . 'templates/verified-account.php',
			'verified'       => UM()->Verified_Users_API()->api()->verified_badge()
		)
	);

}
add_action( 'um_after_user_is_verified', 'um_activity_after_user_verified', 90, 1 );

/* remove activity */
function um_activity_after_user_unverified( $user_id ) {
	if ( ! UM()->options()->get( 'activity-verified-account' ) ) {
		return;
	}

	$args = array(
		'post_type' => 'um_activity',
	);

	$args['meta_query'][] = array( 'key' => '_user_id', 'value' => $user_id, 'compare' => '=' );
	$args['meta_query'][] = array( 'key' => '_related_id', 'value' => $user_id, 'compare' => '=' );
	$args['meta_query'][] = array( 'key' => '_action', 'value' => 'verified-account', 'compare' => '=' );
	$get = new WP_Query( $args );
	if ( $get->found_posts == 0 ) {
		return;
	}
	foreach ( $get->posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
add_action( 'um_after_user_is_unverified', 'um_activity_after_user_unverified', 90, 1 );
