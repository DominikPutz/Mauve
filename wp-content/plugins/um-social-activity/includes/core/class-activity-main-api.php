<?php
namespace um_ext\um_social_activity\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Activity_Main_API
 *
 * @package um_ext\um_social_activity\core
 */
class Activity_Main_API {


	/**
	 * @var mixed|void
	 */
	var $global_actions;


	/**
	 * Activity_Main_API constructor.
	 */
	function __construct() {
		$this->global_actions['status'] = __( 'New wall post', 'um-activity' );
		$this->global_actions['new-user'] = __( 'New user', 'um-activity' );
		$this->global_actions['new-post'] = __( 'New blog post', 'um-activity' );
		$this->global_actions['new-product'] = __( 'New product', 'um-activity' );
		$this->global_actions['new-gform'] = __('New Gravity Form','um-activity');
		$this->global_actions['new-gform-submission'] = __('New Gravity Form Answer','um-activity');
		$this->global_actions['new-follow'] = __( 'New follow', 'um-activity' );

		$this->global_actions = apply_filters( 'um_activity_global_actions', $this->global_actions );

		add_filter( 'um_profile_tabs', array( &$this, 'add_tab' ), 5, 1 );
		add_filter( 'um_user_profile_tabs', array( &$this, 'add_user_tab' ), 5, 1 );
		add_filter( 'pre_kses', array( &$this, 'allow_get_params' ), 10, 1 );
		add_action( 'um_profile_content_activity', array( &$this, 'show_wall' ) );
	}


	/**
	 * API to automate activity posts
	 *
	 * @param array $array
	 * @param bool $update_post
	 * @param null $update_post_id
	 *
	 * @return int|null|\WP_Error
	 */
	function save( $array = array(), $update_post = false, $update_post_id = null ) {
		extract( $array );

		$args = array(
			'post_title'  => '',
			'post_type'   => 'um_activity',
			'post_status' => 'publish',
			'post_author' => $array['author'],
		);

		ob_start();

		$file = ( isset( $array['custom_path'] ) ) ? $array['custom_path'] : um_activity_path . 'templates/html/' . $array['template'] . '.php';
		$theme_file = get_stylesheet_directory() . '/ultimate-member/templates/activity/' . $array['template'] . '.php';
		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}
		if ( file_exists( $file ) ) {
			include $file;
		}
		$args['post_content'] = ob_get_clean();

		$search = array(
			'{author_name}',
			'{author_profile}',
			'{user_name}',
			'{user_profile}',
			'{user_photo}',
			'{post_title}',
			'{post_url}',
			'{post_excerpt}',
			'{post_image}',
			'{price}',
		);
		$search = apply_filters( 'um_activity_search_tpl', $search );

		$replace = array(
			isset( $array['author_name'] ) ? $array['author_name'] : '',
			isset( $array['author_profile'] ) ? $array['author_profile'] : '',
			isset( $array['user_name'] ) ? $array['user_name'] : '',
			isset( $array['user_profile'] ) ? $array['user_profile'] : '',
			isset( $array['user_photo'] ) ? $array['user_photo'] : '',
			isset( $array['post_title'] ) ? $array['post_title'] : '',
			isset( $array['post_url'] ) ? $array['post_url'] : '',
			isset( $array['post_excerpt'] ) ? $array['post_excerpt'] : '',
			isset( $array['post_image'] ) ? $array['post_image'] : '',
			isset( $array['price'] ) ? $array['price'] : '',
		);
		$replace = apply_filters( 'um_activity_replace_tpl', $replace, $array );

		if ( ! in_array( $array['template'], array( 'new-user' ) ) ) {
			$args['post_content'] = str_replace( $search, $replace, $args['post_content'] );
		}

		$args['post_content'] = html_entity_decode( strip_shortcodes( trim( $args['post_content'] ) ) );

		// Update post content
		if ( $update_post ) {
			$args['ID'] = $update_post_id;
			$args['post_title'] = $array['post_title'];
			wp_update_post( $args );

			return $update_post_id;
		}

		$post_id = wp_insert_post( $args );
		wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_id, 'post_name' => $post_id ) );

		update_post_meta( $post_id, '_wall_id', $array['wall_id'] );
		update_post_meta( $post_id, '_action', $array['template'] );
		update_post_meta( $post_id, '_user_id', $array['author'] );
		update_post_meta( $post_id, '_likes', 0 );
		update_post_meta( $post_id, '_comments', 0 );

		if ( isset( $array['related_id'] ) ) {
			update_post_meta( $post_id, '_related_id', absint( $array['related_id'] ) );
		}

		return $post_id;
	}


	/**
	 * Grab followed user IDs
	 *
	 * @return array|null
	 */
	function followed_ids() {
		$array = array();

		if ( ! $this->followed_activity() ) {
			return null;
		}

		if ( ! is_user_logged_in() ) {
			return array( 0 );
		}

		$array[] = get_current_user_id();

		$following = UM()->Followers_API()->api()->following( get_current_user_id() );
		if ( $following ) {
			foreach ( $following as $k => $arr ) {
				$array[] = $arr['user_id1'];
			}
		}

		if ( isset( $array ) ) {
			return $array;
		}

		return null;
	}

	/***
	 ***    @Check if enabled followed activity only
	 ***/
	function followed_activity()
	{
		if ( class_exists( 'UM_Followers_API' ) && UM()->options()->get( 'activity_followed_users' ) )
			return TRUE;

		return FALSE;
	}

	/**
	 * Return to activity post after login
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	function login_to_interact( $post_id = null ) {
		
		$curr_page = UM()->permalinks()->get_current_url();
		if ( !empty( $post_id ) ) {
			$curr_page = add_query_arg( 'wall_post', $post_id, $curr_page );
		}
		
		$pattern = stripslashes( UM()->options()->get( 'activity_need_to_login' ) );

		$text = str_replace( array(
				'{current_page}',
				'{login_page}',
				'{register_page}',
			), array(
				$curr_page,
				add_query_arg( 'redirect_to', $curr_page, um_get_core_page('login') ),
				add_query_arg( 'redirect_to', $curr_page, um_get_core_page('register') ),
			), $pattern );

		return $text;
	}

	/**
	 * Add Profile Tab
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_tab( $tabs ) {
		$tabs['activity'] = array(
			'name'  => __( 'Activity', 'um-activity' ),
			'icon'  => 'um-icon-compose',
		);

		return $tabs;
	}


	/**
	 * Hide Profile Tab if no capabilities
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_user_tab( $tabs ) {
		if ( empty( $tabs['activity'] ) ) {
			return $tabs;
		}

		if ( um_user( 'activity_wall_off' ) ) {
			unset( $tabs['activity'] );
		}

		return $tabs;
	}


	/***
	 ***    @get comment content
	 ***/
	function commentcontent( $content )
	{
		$content = convert_smilies( $content );
		//$content = preg_replace('$(\s|^)(https?://[a-z0-9_./?=&-]+)(?![^<>]*>)$i', ' <a class="um-link" href="$2" target="_blank" rel="nofollow">$2</a> ', $content." ");
		//$content = preg_replace('$(\s|^)(www\.[a-z0-9_./?=&-]+)(?![^<>]*>)$i', '<a class="um-link" target="_blank" href="http://$2"  target="_blank" rel="nofollow">$2</a> ', $content." ");
		$content = $this->make_links_clickable( $content );
		$content = $this->hashtag_links( $content );

		return $content;
	}

	/***
	 ***    @shorten any string based on word count
	 ***/
	function shorten_string( $string )
	{
		$retval = $string;
		$wordsreturned = UM()->options()->get( 'activity_post_truncate' );
		if (!$wordsreturned) return $string;
		$array = explode( " ", $string );
		if (count( $array ) <= $wordsreturned) {
			$retval = $string;
		} else {
			$res = array_splice( $array, $wordsreturned );
			$retval = implode( " ", $array ) . " <span class='um-activity-seemore'>(<a href='' class='um-link'>" . __( 'See more', 'um-activity' ) . "</a>)</span>" . " <span class='um-activity-hiddentext'>" . implode( " ", $res ) . "</span>";
		}

		return $retval;
	}

	/***
	 ***    @can edit a user comment
	 ***/
	function can_edit_comment( $comment_id, $user_id )
	{
		if (!$user_id)
			return FALSE;
		$comment = get_comment( $comment_id );
		if ($comment->user_id == $user_id)
			return TRUE;

		return FALSE;
	}

	/**
	 * Get a summarized content length
	 *
	 * @param int $post_id
	 * @param string $has_video
	 *
	 * @return string
	 */
	function get_content( $post_id = 0, $has_video = '' ) {
		global $post;

		if ( $post_id ) {
			$post = get_post( $post_id );
			$content = $post->post_content;
		} else {
			$post_id = get_the_ID();
			$content = get_the_content();
		}

		$has_attached_photo = get_post_meta( $post_id, '_photo', true );
		$has_oembed = get_post_meta( $post_id, '_oembed', true );

		if ( empty( $has_attached_photo ) || empty( $has_video ) ) {
			$video_content = $this->setup_video( $content, $post_id );
			if ( $video_content['has_video'] == true ) {
				$content = $video_content['content'];
			}
		}

		if ( trim( $content ) != '' ) {

			if ( $this->get_action_type( $post_id ) == 'status' ) {
				$content = $this->shorten_string( $content );
			}
			$content = $this->make_links_clickable( $content );
			$content = $this->hashtag_links( $content );

			// strip avatars
			if ( preg_match( '/\<img src=\"([^\"]+)\" class="(gr)?avatar/', $content, $matches ) ) {
				$src = $matches[1];
				$found = @getimagesize( $src );
				if ( ! $found ) {
					$content = str_replace( $src, um_get_default_avatar_uri(), $content );
				}
			}

			$content = $this->remove_vc_from_excerpt( $content );

			if ( $has_oembed ) {
				$content .= $has_oembed;
			}

			$search = array(
				'{author_name}',
				'{author_profile}',
			);

			$replace = array(
				um_user( 'display_name' ),
				um_user_profile_url(),
			);

			$content = nl2br( str_replace( $search, $replace, $content ) );

			// Replace emojis codes
			$content = convert_smilies( $content );
			if( isset( UM()->shortcodes()->emoji ) ) {
				$content = UM()->shortcodes()->emotize( $content );
			}

			// Add related image if no image
			if ( ! strpos( $content, '<span class="post-image">' ) ) {
				$related_id = get_post_meta( $post_id, '_related_id', true );
				if ( ! empty( $related_id ) ) {
					$post_image_url = $this->get_post_image_url( $related_id );
					if ( $post_image_url ) {
						$post_image = '<span class="post-image"><img src="' . esc_url( $post_image_url ) . '" alt="' . esc_attr( basename( $post_image_url ) ) . '" title="#' . esc_attr( get_the_title( $related_id ) ) . '" class="um-activity-featured-img" /></span>';
						$content = str_replace( '<span class="post-title">', $post_image . '<span class="post-title">', $content );
					}
				}
			}

			return apply_filters( 'um-activity-post-content', $content, $post );
		}

		return '';
	}


	/***
	 ***    @Get content link
	 ***/
	function get_content_link( $content ) {

		$arr_urls = wp_extract_urls( $content );
		if (isset( $arr_urls ) && !empty( $arr_urls )) {
			foreach ($arr_urls as $key => $url) {
				if (
					!strstr( $url, 'vimeo' ) &&
					!strstr( $url, 'youtube' ) &&
					!strstr( $url, 'youtu.be' )
				) {

					return $url;
				}
			}
		}

		return NULL;
	}


	/**
	 * Check if URL is oEmbed supported
	 *
	 * @param $url
	 *
	 * @return bool|false|string
	 */
	function is_oEmbed( $url ) {

		$providers = array(
			'mixcloud.com'   => array( 'height' => 200 ),
			'soundcloud.com' => array( 'height' => 200 ),
			'instagram.com'  => array( 'height' => 500, 'width' => 500 ),
			'twitter.com'    => array( 'height' => 500, 'width' => 700 ),
			't.co'           => array( 'height' => 500, 'width' => 700 ),
		);

		$providers = apply_filters( 'um_activity_oembed_providers', $providers );
		foreach ( $providers as $provider => $size ) {
			if ( strstr( $url, $provider ) ) {
				return wp_oembed_get( $url, $size );
			}
		}

		return false;
	}


	/**
	 * Set url meta
	 *
	 * @param $url
	 * @param $post_id
	 *
	 * @return string
	 */
	function set_url_meta( $url, $post_id ) {

		$request = wp_remote_get( $url );
		$response = wp_remote_retrieve_body( $request );

		$html = new \DOMDocument();
		@$html->loadHTML( mb_convert_encoding( $response, 'HTML-ENTITIES', 'UTF-8' ) );
		$tags = NULL;

		$title = $html->getElementsByTagName( 'title' );
		$tags['title'] = $title->item( 0 )->nodeValue;

		foreach ( $html->getElementsByTagName( 'meta' ) as $meta ) {
			if ( $meta->getAttribute( 'property' ) == 'og:image' ) {
				$tags['image'] = $src = trim( str_replace( '\\', '/', $meta->getAttribute( 'content' ) ) );
				$data = $this->is_image( $src );
				if ( is_array( $data ) ) {
					$tags['image'] = $src;
					$tags['image_width'] = $data[0];
					$tags['image_height'] = $data[1];
				}
			}
			if ( $meta->getAttribute( 'property' ) == 'og:image:width' ) {
				$tags['image_width'] = trim( $meta->getAttribute( 'content' ) );
			}
			if ( $meta->getAttribute( 'property' ) == 'og:image:height' ) {
				$tags['image_height'] = trim( $meta->getAttribute( 'content' ) );
			}
			if ( $meta->getAttribute( 'name' ) == 'description' ) {
				$tags['description'] = trim( str_replace( '\\', '/', $meta->getAttribute( 'content' ) ) );
			}
		}

		if ( ! isset( $tags['image'] ) ) {
			$stop = false;
			foreach ( $html->getElementsByTagName( 'img' ) as $img ) {
				if ( $stop == true ) {
					continue;
				}
				$tags['image'] = $src = trim( str_replace( '\\', '/', $img->getAttribute( 'src' ) ) );
				$data = $this->is_image( $src );
				if ( is_array( $data ) ) {
					$tags['image_width'] = $data[0];
					$tags['image_height'] = $data[1];
					$stop = true;
				}
			}
		}

		/* Display the meta now */

		if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
			$content = '<span class="post-meta" style="position:relative;min-height: ' . ( absint( $tags['image_height'] / 2 ) - 10 ) . 'px;padding-left:' . $tags['image_width'] / 2 . 'px;"><a href="{post_url}" target="_blank">{post_image} {post_title} {post_excerpt} {post_domain}</a></span>';
		} else {
			$content = '<span class="post-meta"><a href="{post_url}" target="_blank">{post_image} {post_title} {post_excerpt} {post_domain}</a></span>';
		}

		if (isset( $tags['description'] )) {
			if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
				$content = str_replace( '{post_excerpt}', '', $content );
			} else {
				$content = str_replace( '{post_excerpt}', '<span class="post-excerpt">' . $tags['description'] . '</span>', $content );
			}
		} else {
			$content = str_replace( '{post_excerpt}', '', $content );
		}

		if (isset( $tags['title'] )) {
			$content = str_replace( '{post_title}', '<span class="post-title">' . mb_convert_encoding( $tags['title'], 'HTML-ENTITIES', 'UTF-8' ) . '</span>', $content );
		} else {
			$content = str_replace( '{post_title}', '<span class="post-title">' . __( 'Untitled', 'um-activity' ) . '</span>', $content );
		}

		if (isset( $tags['image'] )) {
			if (isset( $tags['image_width'] ) && $tags['image_width'] <= 400) {
				$content = str_replace( '{post_image}', '<span class="post-image" style="position:absolute;left:0;top:0;width:' . $tags['image_width'] / 2 . 'px;"><img src="' . $tags['image'] . '" alt="" title="" class="um-activity-featured-img" /></span>', $content );
			} else {
				$content = str_replace( '{post_image}', '<span class="post-image"><img src="' . $tags['image'] . '" alt="" title="" class="um-activity-featured-img" /></span>', $content );
			}
		} else {
			$content = str_replace( '{post_image}', '', $content );
		}

		$parse = parse_url( $url );

		$content = str_replace( '{post_url}', $url, $content );

		$content = str_replace( '{post_domain}', '<span class="post-domain">' . strtoupper( $parse['host'] ) . '</span>', $content );


		update_post_meta( $post_id, '_shared_link', trim( $content ) );

		return trim( $content );

	}

	/***
	 ***    @Checks if image is valid
	 ***/
	function is_image( $url ) {
		$size = @getimagesize( $url );

		$allow_types = array(
			'jpeg' => 'image/jpeg'
		);

		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_allow_mime
		 * @description Extend mime types for images
		 * @input_vars
		 * [{"var":"$allow_types","type":"array","desc":"Allowed Types"}]
		 * @change_log
		 * ["Since: 2.1.8"]
		 * @usage add_filter( 'um_allow_mime', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_filter( 'um_allow_mime', 'my_um_allow_mime', 10, 1 );
		 * function my_um_allow_mime( $allow_types ) {
		 *     // your code here
		 *     return $allow_types;
		 * }
		 * ?>
		 */
		$allow_types = apply_filters( 'um_allow_mime', $allow_types );

		if ( isset( $size['mime'] ) && strstr( $size['mime'], 'image' ) && in_array( $size['mime'], $allow_types ) && isset( $size[0] ) && absint( $size[0] ) > 100 && isset( $size[1] ) && ( $size[0] / $size[1] >= 1 ) && ( $size[0] / $size[1] <= 3 ) ) {
			return $size;
		}

		return 0;
	}


	/**
	 * Convert hashtags
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	function hashtag_links( $content ) {
		preg_match_all( '/>[^<]*#([^\s<]+)/', $content, $matches );

		if ( isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( array_unique( $matches[1] ) as $match ) {
				$link = '<strong onclick="window.location.assign(\'' . add_query_arg( 'hashtag', $match, um_get_core_page( 'activity' ) ) . '\'); return false;" class="um-link">#' . $match . '</strong>';
				$content = str_replace( '#' . $match, $link, $content );
			}
		}

		return $content;
	}


	/**
	 * Add hashtags
	 *
	 * @param int $post_id
	 * @param string $content
	 * @param bool $append
	 */
	function hashtagit( $post_id, $content, $append = false ) {
		preg_match_all( '/(?<!\&)#([^\s]+)/', $content, $matches );
		if ( isset( $matches[1] ) && is_array( $matches[1] )) {
			wp_set_post_terms( $post_id, $matches[1], 'um_hashtag', $append );
		}
	}


	/**
	 * Get a possible photo
	 *
	 * @param int $post_id
	 * @param string $class
	 * @param null $author_id
	 *
	 * @return string
	 */
	function get_photo( $post_id = 0, $class = '', $author_id = null ) {
		$photo_url = $this->get_download_link( $post_id, $author_id );
		if ( empty( $photo_url ) ) {
			return '';
		}
		$photo_url = esc_attr( $photo_url );

		$content = '';
		if ( 'backend' == $class ) {
			$uri = get_post_meta( $post_id, '_photo', true );
			if ( ! $uri ) {
				return '';
			}
			$uri = wp_basename( $uri );
			$user_base_dir = UM()->uploader()->get_upload_user_base_dir( $author_id );

			if ( file_exists( $user_base_dir . DIRECTORY_SEPARATOR . $uri ) ) {
				$content = "<a href=\"{$photo_url}\" target=\"_blank\"><img src=\"{$photo_url}\" alt=\"\" style=\"width: 100%;\" /></a>";
			}
		} else {
			$content = "<a href=\"#\" class=\"um-photo-modal\" data-src=\"{$photo_url}\"><img src=\"{$photo_url}\" alt=\"\" /></a>";
		}

		return $content;
	}


	/**
	 * @param int $post_id
	 * @param int $author_id
	 *
	 * @return string
	 */
	function get_download_link( $post_id, $author_id ) {
		$uri = get_post_meta( $post_id, '_photo', true );

		if ( ! $uri ) {
			return '';
		}

		if ( UM()->is_permalinks ) {
			$url = get_home_url( get_current_blog_id() );
			$nonce = wp_create_nonce( $author_id . $post_id . 'um-download-nonce' );
			$url = $url . "/um-activity-download/{$post_id}/{$author_id}/{$nonce}";
		} else {
			$url = get_home_url( get_current_blog_id() );
			$nonce = wp_create_nonce( $author_id . $post_id . 'um-download-nonce' );
			$url = add_query_arg( array( 'um_action' => 'um-activity-download', 'um_form' => $post_id, 'um_user' => $author_id, 'um_verify' => $nonce ), $url );
		}

		return add_query_arg( array( 't' => time() ), $url );
	}



	/**
	 * Get a possible video
	 *
	 * @param int $post_id
	 * @param array $args
	 *
	 * @return false|string
	 */
	function get_video( $post_id = 0, $args = array() ) {
		$uri = get_post_meta( $post_id, '_video_url', true );
		if ( ! $uri ) {
			return '';
		}

		$content = wp_oembed_get( $uri, $args );
		return $content;
	}


	/***
	 ***    @strip video URLs as we need to convert them
	 ***/
	function setup_video( $content, $post_id )
	{
		preg_match_all( "#(https?://vimeo.com)/([0-9]+)#i", $content, $matches1 );
		preg_match_all( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $content, $matches2 );
		$has_video = FALSE;
		if (isset( $matches1 ) && isset( $matches1[0] )) {
			foreach ($matches1[0] as $key => $val) {
				$videos[] = trim( $val );
			}
		}
		if (isset( $matches2[0] )) {
			foreach ($matches2[0] as $key => $val) {
				$videos[] = trim( $val );
			}
		}
		if (isset( $videos )) {
			$content = str_replace( $videos[0], '', $content );
			update_post_meta( $post_id, '_video_url', $videos[0] );
			$has_video = TRUE;
		} else {
			delete_post_meta( $post_id, '_video_url' );
		}

		return array( 'has_video' => $has_video, 'content' => $content );
	}

	/***
	 ***    @can post on that wall
	 ***/
	function can_write()
	{
		$res = 1;

		if (UM()->roles()->um_user_can( 'activity_posts_off' ))
			$res = 0;

		if (!is_user_logged_in())
			$res = 0;

		$res = apply_filters( 'um_activity_can_post_on_wall', $res );

		return $res;
	}

	/***
	 ***    @can comment on wall
	 ***/
	function can_comment()
	{
		$res = 1;

		if (UM()->roles()->um_user_can( 'activity_comments_off' ))
			$res = 0;

		if (!is_user_logged_in())
			$res = 0;

		$res = apply_filters( 'um_activity_can_post_comment_on_wall', $res );

		return $res;
	}


	/**
	 * User Profile Activity Wall
	 */
	function show_wall() {
		$profile_id = um_profile_id();
		$can_view = $this->can_view_wall( um_profile_id() );

		UM()->Activity_API()->enqueue()->enqueue_scripts();

		if ( $can_view === true ) {
			echo do_shortcode( '[ultimatemember_wall user_id="' . $profile_id . '"]' );
		} else { ?>
			<div class="um-profile-note">
				<span>
					<i class="um-faicon-lock"></i>
					<?php echo esc_html( $can_view ) ?>
				</span>
			</div>
		<?php }
	}


	/**
	 * @param int $profile_id
	 *
	 * @return bool|string
	 */
	function can_view_wall( $profile_id ) {
		$can_view = true;

		if ( ! UM()->options()->get( 'activity_enable_privacy' ) ) {
			return $can_view;
		}

		$privacy = get_user_meta( $profile_id, 'wall_privacy', true );

		if ( ! is_user_logged_in() ) {
			if ( UM()->options()->get( 'activity_require_login' ) ) {
				$can_view = __( 'You must login to view this user activity', 'um-activity' );
			} elseif ( $privacy == 1 ) {
				$can_view = __( 'Please login to view this user\'s activity', 'um-activity' );
			} elseif ( $privacy == 2 ) {
				$can_view = __( 'This user wall is private', 'um-activity' );
			}
		} else {
			if ( $profile_id != get_current_user_id() && $privacy == 2 ) {
				$can_view = __( 'This user wall is private', 'um-activity' );
			}
		}

		return apply_filters( 'um_wall_can_view', $can_view, $profile_id );
	}


	/***
	 ***    @cice time difference
	 ***/
	function human_time_diff( $from, $to = '' )
	{
		if (empty( $to )) {
			$to = time();
		}
		$diff = (int)abs( $to - $from );
		if ($diff < 60) {

			$since = __( 'Just now', 'um-activity' );

		} else if ($diff < HOUR_IN_SECONDS) {

			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ($mins <= 1)
				$mins = 1;
			if ($mins == 1) {
				$since = sprintf( __( '%s min', 'um-activity' ), $mins );
			} else {
				$since = sprintf( __( '%s mins', 'um-activity' ), $mins );
			}

		} else if ($diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS) {

			$hours = round( $diff / HOUR_IN_SECONDS );
			if ($hours <= 1)
				$hours = 1;
			if ($hours == 1) {
				$since = sprintf( __( '%s hr', 'um-activity' ), $hours );
			} else {
				$since = sprintf( __( '%s hrs', 'um-activity' ), $hours );
			}

		} else if ($diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS) {

			$days = round( $diff / DAY_IN_SECONDS );
			if ($days <= 1)
				$days = 1;
			if ($days == 1) {
				$since = sprintf( __( 'Yesterday at %s', 'um-activity' ), date_i18n( 'g:ia', $from ) );
			} else {
				$since = sprintf( __( '%s at %s', 'um-activity' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );
			}

		} else if ($diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-activity' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );

		} else if ($diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-activity' ), date_i18n( 'F d', $from ), date_i18n( 'g:ia', $from ) );

		} else if ($diff >= YEAR_IN_SECONDS) {

			$since = sprintf( __( '%s at %s', 'um-activity' ), date_i18n( 'F d, Y', $from ), date_i18n( 'g:ia', $from ) );

		}

		return apply_filters( 'um_activity_human_time_diff', $since, $diff, $from, $to );
	}

	/**
	 * Get faces of people who liked
	 *
	 * @param $post_id
	 * @param int $num
	 *
	 * @return string
	 */
	function get_faces( $post_id, $num = 10 ) {
		$res = '';
		$limit = UM()->options()->get( 'activity_max_faces' );
		if ( ! $limit ) {
			$limit = 10;
		}

		$i = 0;
		$users = get_post_meta( $post_id, '_liked', true );
		if ( $users && is_array( $users ) ) {
			$users = array_reverse( $users );
			$users = array_slice( $users, 0, $num );
			foreach ( $users as $user_id ) {
				if ( absint( $user_id ) && $user_id ) {
					
					$res .= get_avatar( $user_id, 80 );
					
					$i++;
					if ( $i >= $limit ) {
						break;
					}
				}
			}
		}

		return '<a href="#" data-post_id="' . esc_attr( $post_id ) . '" class="um-activity-show-likes um-tip-s" title="' . esc_attr__( 'People who like this', 'um-activity' ) . '">' . $res . '</a>';
	}


	/**
	 * Hide a comment for user
	 *
	 * @param $comment_id
	 */
	function user_hide_comment( $comment_id ) {
		$user_id = get_current_user_id();

		//hide comment replies
		$comment_data = get_comment( $comment_id );
		if ( 0 == $comment_data->comment_parent ) {
			$replies = get_comments( array(
				'post_id'   => $comment_data->comment_post_ID,
				'parent'    => $comment_id,
				'number'    => 10000,
				'offset'    => 0,
				'fields' => 'ids'
			) );

			if ( ! empty( $replies ) && ! is_wp_error( $replies ) ) {
				foreach ( $replies as $reply_id ) {
					$this->user_hide_comment( $reply_id );
				}
			}
		}

		$users = get_comment_meta( $comment_id, '_hidden_from', true );

		if ( empty( $users ) || ! is_array( $users ) ) {
			$users = array();
		}

		$users[ $user_id ] = current_time( 'timestamp' );

		update_comment_meta( $comment_id, '_hidden_from', $users );
	}


	/**
	 * Unhide a comment for user
	 *
	 * @param $comment_id
	 */
	function user_unhide_comment( $comment_id ) {
		$users = get_comment_meta( $comment_id, '_hidden_from', true );

		$user_id = get_current_user_id();

		if ( isset( $users[ $user_id ] ) ) {
			unset( $users[ $user_id ] );
		}

		if ( ! $users ) {
			delete_comment_meta( $comment_id, '_hidden_from' );
		} else {
			update_comment_meta( $comment_id, '_hidden_from', $users );
		}
	}


	/**
	 * Checks if user hidden comment
	 *
	 * @param $comment_id
	 *
	 * @return int
	 */
	function user_hidden_comment( $comment_id ) {
		$users = get_comment_meta( $comment_id, '_hidden_from', true );
		$user_id = get_current_user_id();

		if ( $users && is_array( $users ) && isset( $users[ $user_id ] ) ) {
			return 1;
		}

		return 0;
	}


	/***
	 ***    @Checks if user liked specific wall comment
	 ***/
	function user_liked_comment( $comment_id )
	{
		$res = '';
		$users = get_comment_meta( $comment_id, '_liked', TRUE );
		if ($users && is_array( $users ) && in_array( get_current_user_id(), $users ))
			return TRUE;

		return FALSE;
	}

	/***
	 ***    @Checks if user liked specific wall post
	 ***/
	function user_liked( $post_id )
	{
		$res = '';
		$users = get_post_meta( $post_id, '_liked', TRUE );
		if ($users && is_array( $users ) && in_array( get_current_user_id(), $users ))
			return TRUE;

		return FALSE;
	}

	/***
	 ***    @Checks if post is reported
	 ***/
	function reported( $post_id, $reporter_id = null )
	{
		$reported = get_post_meta( $post_id, '_reported', TRUE );
		if( $reporter_id ){
			$reported_by = get_post_meta( $post_id,'_reported_by', TRUE );
			if( isset( $reported_by[ $reporter_id ] ) ){
				return 1;
			}

			return 0;
		}
		
		return ( $reported ) ? 1 : 0;
	}

	/***
	 ***    @Gets action name
	 ***/
	function get_action( $post_id )
	{
		$action = (string)get_post_meta( $post_id, '_action', TRUE );
		$action = ( $action ) ? $action : 'status';

		return isset( $this->global_actions[$action] ) ? $this->global_actions[$action] : '';
	}

	/***
	 ***    @Gets action type
	 ***/
	function get_action_type( $post_id )
	{
		$action = (string)get_post_meta( $post_id, '_action', TRUE );
		$action = ( $action ) ? $action : 'status';

		return $action;
	}

	/***
	 ***    @Get comment time
	 ***/
	function get_comment_time( $time )
	{
		$timestamp = strtotime( $time );
		$time = $this->human_time_diff( $timestamp, current_time( 'timestamp' ) );

		return $time;
	}

	/**
	 * Get comment link
	 *
	 * @param string $post_link
	 * @param int $comment_id
	 *
	 * @return string
	 */
	function get_comment_link( $post_link, $comment_id ) {
		$link = add_query_arg( 'wall_comment_id', $comment_id, $post_link );
		return $link;
	}


	/***
	 ***    @Gets activity in nice time format
	 ***/
	function get_post_time( $post_id )
	{
		$time = $this->human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) );

		return apply_filters( 'um_activity_human_post_time', $time, $post_id );
	}


	/**
	 * Gets post permalink
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	function get_permalink( $post_id ) {
		$url = um_get_core_page( 'activity' );
		return add_query_arg( 'wall_post', $post_id, $url );
	}


	/**
	 * Gets post author
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	function get_author( $post_id ) {
		$author = (int)get_post_meta( $post_id, '_user_id', true );
		return ( $author ) ? $author : 0;
	}


	/**
	 * Gets post wall ID
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	function get_wall( $post_id ) {
		$wall = (int)get_post_meta( $post_id, '_wall_id', true );
		return ( $wall ) ? $wall : 0;
	}


	/**
	 * Get likes count
	 *
	 * @param $post_id
	 *
	 * @return int
	 */
	function get_likes_number( $post_id ) {
		return (int)get_post_meta( $post_id, '_likes', true );
	}


	/**
	 * Get comment count
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	function get_comments_number( $post_id ) {
		$comments_all = get_comments( array(
			'post_id'   => $post_id,
			'parent'    => 0,
			'number'    => 10000,
			'offset'    => 0
		) );
		return count( $comments_all );
	}


	/**
	 * Get replies count
	 *
	 * @param int $post_id
	 * @param int $comment_id
	 *
	 * @return int
	 */
	function get_replies_number( $post_id, $comment_id ) {
		$replies_all = get_comments( array(
			'post_id'   => $post_id,
			'parent'    => $comment_id,
			'number'    => 10000,
			'offset'    => 0
		) );
		return count( $replies_all );
	}


	/**
	 * Make links clickable
	 *
	 * @param $content
	 *
	 * @return mixed|null|string|string[]
	 */
	function make_links_clickable( $content ) {
		$has_iframe = preg_match( '/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $content, $matches );

		if ($has_iframe) {
			$content = preg_replace( '/<iframe.*?\/iframe>/i', '[um_activity_iframe]', $content );
		}

		$content = preg_replace( '/(<a\b[^><]*)>/i', '$1 class="um-link" target="_blank">', make_clickable( $content ) );

		if ($has_iframe && isset( $matches[0] )) {
			$content = str_replace( '[um_activity_iframe]', $matches[0], $content );
		}

		return $content;
	}


	/**
	 * Removes Visual Composer's shortcodes
	 *
	 * @param  string $excerpt
	 *
	 * @return string
	 */
	function remove_vc_from_excerpt( $excerpt ) {
		$patterns = "/\[[\/]?vc_[^\]]*\]/";
		$replacements = "";

		return preg_replace( $patterns, $replacements, $excerpt );
	}


	/***
	 ***    @Check if enabled friends activity only
	 ***/
	function friends_activity() {
		if (class_exists( 'UM_Friends_API' ) && UM()->options()->get( 'activity_friends_users' )) {
			return true;
		}

		return false;
	}


	/**
	 * Grab friends user ids
	 *
	 * @return array|null
	 */
	function friends_ids() {
		$array = array();

		if ( ! $this->friends_activity() ) {
			return null;
		}

		if ( ! is_user_logged_in() ) {
			return array( 0 );
		}

		$array[] = get_current_user_id();

		$friends = UM()->Friends_API()->api()->friends( get_current_user_id() );
		if ($friends) {
			foreach ($friends as $k => $arr) {
				if ($arr['user_id1'] == get_current_user_id()) {
					$array[] = $arr['user_id2'];
				} else {
					$array[] = $arr['user_id1'];
				}
			}
		}

		if (isset( $array ))
			return $array;

		return NULL;
	}


	/**
	 * Load wall posts
	 */
	function ajax_load_wall() {
		UM()->check_ajax_nonce();

		$offset = absint( $_POST['offset'] );
		$wall_post = absint( $_POST['wall_post'] );
		$user_id = absint( $_POST['user_id'] );
		$user_wall = !empty( $_POST['user_wall'] ) && sanitize_key( $_POST['user_wall'] ) !== 'false';
		$hashtag = isset( $_POST['hashtag'] ) ? sanitize_key( $_POST['hashtag'] ) : '';

		UM()->Activity_API()->shortcode()->args = $t_args = array(
			'user_wall' => $user_wall,
			'user_id'   => $user_id,
			'hashtag'   => $hashtag,
			'offset'    => $offset,
			'wall_post' => $wall_post
		);
		UM()->get_template( 'user-wall.php', um_activity_plugin, $t_args, true );

		die();
	}


	/**
	 * Get user suggestions
	 */
	function ajax_get_user_suggestions() {
		UM()->check_ajax_nonce();

		if ( ! is_user_logged_in() ) {
			die();
		}

		$data = apply_filters( 'um_activity_ajax_get_user_suggestions', array(), sanitize_key( $_GET['term'] ) );
		$data = array_unique( $data, SORT_REGULAR );

		wp_send_json( $data );
	}


	/**
	 * Removes a wall post
	 */
	function ajax_remove_post() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['post_id'] ) || absint( $_POST['post_id'] ) <= 0 ) {
			die();
		}

		$post_id = absint( $_POST['post_id'] );

		$author_id = $this->get_author( $post_id );

		if (current_user_can( 'edit_users' )) {
			wp_delete_post( $post_id, TRUE );
		} else if ($author_id == get_current_user_id() && is_user_logged_in()) {
			wp_delete_post( $post_id, TRUE );
		}

		die();
	}


	/**
	 * Removes a wall comment via AJAX
	 */
	function ajax_remove_comment() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['comment_id'] ) || absint( $_POST['comment_id'] ) <= 0 ) {
			die();
		}

		$comment_id = absint( $_POST['comment_id'] );

		if ( $this->can_edit_comment( $comment_id, get_current_user_id() ) ) {
			$this->delete_comment( $comment_id );
		}

		die();
	}


	/**
	 * Remove comment with all replies
	 *
	 * @param $comment_id
	 */
	function delete_comment( $comment_id ) {
		global $wpdb;

		$comment = get_comment( $comment_id );

		//remove comment replies
		if ( 0 == $comment->comment_parent ) {
			$replies = get_comments( array(
				'post_id'   => $comment->comment_post_ID,
				'parent'    => $comment_id,
				'number'    => 10000,
				'offset'    => 0,
				'fields'    => 'ids'
			) );

			if ( ! empty( $replies ) && ! is_wp_error( $replies ) ) {
				foreach ( $replies as $reply_id ) {
					$this->delete_comment( $reply_id );
				}
			}
		}

		// remove comment
		wp_delete_comment( $comment_id, true );

		// remove hashtag(s) from the trending list if it's
		// totally remove from posts / comments
		$content = $comment->comment_content;
		$post_id = $comment->comment_post_ID;
		preg_match_all( '/(?<!\&)#([^\s\<]+)/', $content, $matches );
		if ( isset( $matches[1] ) && is_array( $matches[1] ) ) {
			foreach ( $matches[1] as $hashtag ) {
				$post_count = intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) 
						FROM {$wpdb->posts} 
						WHERE ID = %d AND 
							  post_content LIKE %s",
					$post_id,
					"%>#{$hashtag}<%"
				) ) );
				$comment_count = intval( $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) 
						FROM {$wpdb->comments} 
						WHERE comment_post_ID = %d AND 
							  comment_content LIKE %s",
					$post_id,
					"%>#{$hashtag}<%"
				) ) );

				if ( ! $post_count && ! $comment_count ) {
					$term = get_term_by( 'name', $hashtag, 'um_hashtag' );
					wp_remove_object_terms( $post_id, $term->term_id, 'um_hashtag' );
				}
			}
		}
	}


	/**
	 * Load post likes via AJAX
	 */
	function ajax_get_post_likes() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['post_id'] ) || absint( $_POST['post_id'] ) <= 0 ) {
			die();
		}

		$item_id = absint( $_POST['post_id'] );

		if ( ! $item_id ) {
			die();
		}

		$users = get_post_meta( $item_id, '_liked', true );
		if ( ! $users || ! is_array( $users ) ) {
			die();
		}

		$users = array_reverse( $users );

		ob_start();

		$file = um_activity_path . 'templates/likes.php';
		$theme_file = get_stylesheet_directory() . '/ultimate-member/templates/activity/likes.php';

		if (file_exists( $theme_file ))
			$file = $theme_file;

		if (file_exists( $file ))
			include $file;

		$output = ob_get_contents();
		ob_end_clean();
		die( $output );

	}

	/***
	 ***    @load comment likes
	 ***/
	function ajax_get_comment_likes() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['comment_id'] ) || absint( $_POST['comment_id'] ) <= 0 ) {
			die();
		}

		$item_id = absint( $_POST['comment_id'] );

		if ( ! $item_id ) {
			die();
		}

		$users = get_comment_meta( $item_id, '_liked', true );
		if ( ! $users || ! is_array( $users ) ) {
			die();
		}

		$users = array_reverse( $users );

		ob_start();

		$file = um_activity_path . 'templates/likes.php';
		$theme_file = get_stylesheet_directory() . '/ultimate-member/templates/activity/likes.php';

		if (file_exists( $theme_file ))
			$file = $theme_file;

		if (file_exists( $file ))
			include $file;

		$output = ob_get_contents();
		ob_end_clean();
		die( $output );

	}


	/**
	 * Hide a comment via AJAX
	 */
	function ajax_hide_comment() {
		UM()->check_ajax_nonce();

		if ( ! is_user_logged_in() ) {
			die();
		}

		$comment_id = absint( $_POST['comment_id'] );
		if ( $comment_id <= 0 ) {
			die();
		}
		$this->user_hide_comment( $comment_id );
		die();
	}


	/**
	 * Unhide a comment via AJAX
	 */
	function ajax_unhide_comment() {
		UM()->check_ajax_nonce();

		if ( ! is_user_logged_in() ) {
			die();
		}

		$comment_id = absint( $_POST['comment_id'] );
		if ( $comment_id <= 0 ) {
			die();
		}

		$this->user_unhide_comment( $comment_id );
		die();
	}

	/***
	 ***    @report a post
	 ***/
	function ajax_report_post() {
		UM()->check_ajax_nonce();

		if ( ! is_user_logged_in( ) ) {
			die();
		}

		$post_id = absint( $_POST['post_id'] );
		if ( $post_id <= 0 ) {
			die();
		}

		$user_id = get_current_user_id();
		
		$users_reported = get_post_meta( $post_id, '_reported_by', true );
		if ( ! isset( $users_reported[ $user_id ] ) ) {
			$users_reported[ $user_id ] = current_time( 'timestamp' );
			update_post_meta( $post_id, '_reported_by', $users_reported );
		}
		
		if ( ! get_post_meta( $post_id, '_reported', true ) ) {
			$count = (int) get_option( 'um_activity_flagged' );
			update_option( 'um_activity_flagged', $count + 1 );
		}

		$new_r = (int) get_post_meta( $post_id, '_reported', true );
		update_post_meta( $post_id, '_reported', $new_r + 1 );

		die();

	}


	/***
	 ***    @un-report a post
	 ***/
	function ajax_unreport_post() {
		UM()->check_ajax_nonce();

		if ( ! is_user_logged_in() ) {
			die();
		}

		$post_id = absint( $_POST['post_id'] );
		$user_id = get_current_user_id();
		
		if ( $post_id <= 0 ) die();

		$users_reported = get_post_meta( $post_id, '_reported_by', true );
		if ( isset( $users_reported[ $user_id ] ) ) {
			unset( $users_reported[ $user_id ] );
		}

		if ( ! $users_reported  ) {
			$user_reported = "";
		}

		update_post_meta( $post_id, '_reported_by', $users_reported );

		if ( get_post_meta( $post_id, '_reported', true ) ) {

			$new_r = (int) get_post_meta( $post_id, '_reported', true );
			$new_r = $new_r - 1;
			if ($new_r < 0) $new_r = 0;
			update_post_meta( $post_id, '_reported', $new_r );

			if ($new_r == 0) {
				$count = (int)get_option( 'um_activity_flagged' );
				update_option( 'um_activity_flagged', absint( $count - 1 ) );
			}

		}

		die();

	}


	/**
	 * Load wall comments via AJAX
	 */
	function ajax_load_more_comments() {
		UM()->check_ajax_nonce();

		$number = UM()->options()->get( 'activity_load_comments_count' );
		$offset = absint( $_POST['offset'] );
		$post_id = absint( $_POST['post_id'] );
		$post_link = $this->get_permalink( $post_id );

		$comments = get_comments( array(
			'post_id'   => $post_id,
			'parent'    => 0,
			'number'    => $number,
			'offset'    => $offset,
			'order'     => UM()->options()->get( 'activity_order_comment' )
		) );
		$comments_all = $this->get_comments_number( $post_id );

		UM()->Activity_API()->shortcode()->args = $t_args = array(
			'comments'  => $comments,
			'post_id'   => $post_id,
			'post_link' => $post_link
		);
		UM()->get_template( 'comment.php', um_activity_plugin, $t_args, true );

		if ( $comments_all > ( $offset + $number ) ) {
			?><span class="um-activity-commentload-end"></span><?php
		}
		
		die();
	}


	/**
	 * Load wall replies via AJAX
	 */
	function ajax_load_more_replies() {
		UM()->check_ajax_nonce();

		$number = UM()->options()->get( 'activity_load_comments_count' );

		$offset = absint( $_POST['offset'] );
		$post_id = absint( $_POST['post_id'] );
		$comment_id = absint( $_POST['comment_id'] );
		$post_link = $this->get_permalink( $post_id );

		$child = get_comments( array(
			'post_id'   => $post_id,
			'parent'    => $comment_id,
			'number'    => $number,
			'offset'    => $offset,
			'order'     => UM()->options()->get( 'activity_order_comment' )
		) );
		$child_all = $this->get_replies_number( $post_id, $comment_id );

		ob_start();

		foreach ( $child as $commentc ) {
			um_fetch_user( $commentc->user_id );

			UM()->Activity_API()->shortcode()->args = $t_args = array(
				'commentc'  => $commentc,
				'post_id'   => $post_id,
				'post_link' => $post_link
			);
			UM()->get_template( 'comment-reply.php', um_activity_plugin, $t_args, true );
		}

		if ( $child_all > ( $offset + $number ) ) { ?>
			<span class="um-activity-ccommentload-end"></span>
		<?php }

		ob_get_flush();
		die();
	}


	/***
	 ***    @like wall comment
	 ***/
	function ajax_like_comment() {
		UM()->check_ajax_nonce();

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You must login to like', 'um-activity' );
		}

		if ( ! isset( $_POST['commentid'] ) || ! is_numeric( $_POST['commentid'] ) ) {
			$output['error'] = __( 'Invalid comment', 'um-activity' );
		}

		if ( ! $output['error'] ) {

			$comment_id = absint( $_POST['commentid'] );

			$likes = (int) get_comment_meta( $comment_id, '_likes', true );
			update_comment_meta( $comment_id, '_likes', $likes + 1 );

			$liked = get_comment_meta( $comment_id, '_liked', true );
			if ( ! $liked ) {
				$liked = array( get_current_user_id() );
			} else {
				$liked[] = get_current_user_id();
			}
			update_comment_meta( $comment_id, '_liked', $liked );

		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}

	/***
	 ***    @unlike wall comment
	 ***/
	function ajax_unlike_comment() {
		UM()->check_ajax_nonce();

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You must login to unlike', 'um-activity' );
		}

		if ( ! isset( $_POST['commentid'] ) || ! is_numeric( $_POST['commentid'] ) ) {
			$output['error'] = __( 'Invalid comment', 'um-activity' );
		}

		if ( ! $output['error'] ) {
			$comment_id = absint( $_POST['commentid'] );

			$likes = get_comment_meta( $comment_id, '_likes', true );
			update_comment_meta( $comment_id, '_likes', $likes - 1 );

			$liked = get_comment_meta( $comment_id, '_liked', true );
			if ( $liked ) {
				$liked = array_diff( $liked, array( get_current_user_id() ) );
			}
			update_comment_meta( $comment_id, '_liked', $liked );

		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}

	/***
	 ***    @like wall post
	 ***/
	function ajax_like_post() {
		UM()->check_ajax_nonce();

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You must login to like', 'um-activity' );
		}

		if ( ! isset( $_POST['postid'] ) || ! is_numeric( $_POST['postid'] ) ) {
			$output['error'] = __( 'Invalid wall post', 'um-activity' );
		}

		if ( ! $output['error'] ) {

			$post_id = absint( $_POST['postid'] );

			$likes = get_post_meta( $post_id, '_likes', true );
			update_post_meta( $post_id, '_likes', $likes + 1 );

			$liked = get_post_meta( $post_id, '_liked', true );
			if ( ! $liked ) {
				$liked = array( get_current_user_id() );
			} else {
				$liked[] = get_current_user_id();
			}
			update_post_meta( $post_id, '_liked', $liked );

			do_action( 'um_activity_after_wall_post_liked', $_POST['postid'], get_current_user_id() );

		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}

	/***
	 ***    @unlike wall post
	 ***/
	function ajax_unlike_post() {
		UM()->check_ajax_nonce();

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You must login to unlike', 'um-activity' );
		}

		if ( ! isset( $_POST['postid'] ) || ! is_numeric( $_POST['postid'] ) ) {
			$output['error'] = __( 'Invalid wall post', 'um-activity' );
		}

		if ( ! $output['error'] ) {

			$post_id = absint( $_POST['postid'] );

			$likes = get_post_meta( $post_id, '_likes', true );
			update_post_meta( $post_id, '_likes', $likes - 1 );

			$liked = get_post_meta( $post_id, '_liked', true );
			if ($liked) {
				$liked = array_diff( $liked, array( get_current_user_id() ) );
			}
			update_post_meta( $post_id, '_liked', $liked );

		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}


	/**
	 * Add a new wall post comment via AJAX
	 */
	function ajax_wall_comment() {
		UM()->check_ajax_nonce();

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'Login to post a comment', 'um-activity' );
		}

		if ( ! isset( $_POST['postid'] ) || ! is_numeric( $_POST['postid'] ) ) {
			$output['error'] = __( 'Invalid wall post', 'um-activity' );
		}

		if ( ! isset( $_POST['comment'] ) || trim( $_POST['comment'] ) == '' ) {
			$output['error'] = __( 'Enter a comment first', 'um-activity' );
		}

		if ( ! $output['error'] ) {

			um_fetch_user( get_current_user_id() );

			$time = current_time( 'mysql' );

			if ( isset( $_POST['postid'] ) ) {
				$post_id = absint( $_POST['postid'] );
			}
			$orig_content = trim( $_POST['comment'] );
			$comment_content = wp_kses( trim( $_POST['comment'] ), array(
				'br' => array()
			) );
			$comment_content = apply_filters( 'um_activity_comment_content_new', $comment_content, $post_id );
			// apply hashtag
			$this->hashtagit( $post_id, $comment_content );

			$comment_content = $this->hashtag_links( $comment_content );
			$comment_content = apply_filters( 'um_activity_insert_post_content_filter', $comment_content, get_current_user_id(), absint( $post_id ), 'new' );

			um_fetch_user( get_current_user_id() );

			$data = array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => um_user( 'display_name' ),
				'comment_author_email' => um_user( 'user_email' ),
				'comment_author_url'   => um_user_profile_url(),
				'comment_content'      => $comment_content,
				'user_id'              => get_current_user_id(),
				'comment_approved'     => 1,
				'comment_author_IP'    => um_user_ip(),
				'comment_type'         => 'um-social-activity'
			);

			$comment_content = $this->make_links_clickable( $comment_content );
			$output['comment_content'] = stripslashes_deep( $comment_content );

			if ( isset( $_POST['reply_to'] ) && absint( $_POST['reply_to'] ) ) {
				$data['comment_parent'] = absint( $_POST['reply_to'] );
			} else {
				$data['comment_parent'] = 0;
			}

			if ( ! empty( $_POST['commentid'] ) ) {
				$data['comment_ID'] = $commentid = absint( $_POST['commentid'] );
				wp_update_comment( $data );
			} else {
				$data['comment_date'] = $time;
				$commentid = wp_insert_comment( $data );
			}

			update_comment_meta( $commentid, 'orig_content', $orig_content );

			if ( isset( $_POST['reply_to'] ) && absint( $_POST['reply_to'] ) ) {
				$comment_parent = $data['comment_parent'];
				do_action( 'um_activity_after_wall_comment_reply_published', $commentid, $comment_parent, $post_id, get_current_user_id() );
			} else {
				$comment_parent = 0;
			}

			$comment_count = get_post_meta( $post_id, '_comments', true );
			update_post_meta( $post_id, '_comments', $comment_count + 1 );

			$likes = get_comment_meta( $commentid, '_likes', true );

			$output['commentid'] = $commentid;
			$output['user_hidden'] = $this->user_hidden_comment( $commentid );
			$output['permalink'] = $this->get_comment_link( $this->get_permalink( $post_id ), $commentid );
			$output['time'] = $this->get_comment_time( $time );
			$output['can_edit_comment'] = $this->can_edit_comment( $commentid, get_current_user_id() );
			$output['user_liked_comment'] = $this->user_liked_comment( $commentid );
			$output['likes'] = empty( $likes ) ? 0 : $likes;

			do_action( 'um_activity_after_wall_comment_published', $commentid, $comment_parent, $post_id, get_current_user_id() );
		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}


	/**
	 * Add a new wall post via AJAX
	 */
	function ajax_activity_publish() {
		UM()->check_ajax_nonce();

		/**
		 * @var $_post_content
		 * @var $_post_img
		 * @var $has_oEmbed
		 */
		extract( $_POST );

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You can not post as guest', 'um-activity' );
		}

		if ( $_post_content == '' || trim( $_post_content ) == '' ) {
			if ( trim( $_post_img ) == '' ) {
				$output['error'] = __( 'You should type something first', 'um-activity' );
			}
		}

		if ( ! $output['error'] ) {

			$has_oEmbed = false;

			if ( absint( $_POST['_post_id'] ) == 0 ) {

				$args = array(
					'post_title'  => '',
					'post_type'   => 'um_activity',
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
				);

				$output['link'] = '';

				if ( trim( $_post_content ) ) {
					$orig_content = trim( $_post_content );
					$safe_content = wp_kses( $_post_content, array(
						'br' => array()
					) );

					// shared a link
					$shared_link = $this->get_content_link( $safe_content );
					$has_oEmbed = $this->is_oEmbed( $shared_link );

					if ( isset( $shared_link ) && $shared_link && ! $_post_img && ! $has_oEmbed ) {
						$safe_content = str_replace( $shared_link, '', $safe_content );
						$output['_shared_link'] = $shared_link;
					}

					$args['post_content'] = $safe_content;
				}

				$args = apply_filters( 'um_activity_insert_post_args', $args );

				$post_id = wp_insert_post( $args );

				// shared a link
				if ( isset( $shared_link ) && $shared_link && ! $_post_img && ! $has_oEmbed ) {
					$output['link'] = $this->set_url_meta( $shared_link, $post_id );
				} else {
					delete_post_meta( $post_id, '_shared_link' );
				}

				$args['post_content'] = apply_filters( 'um_activity_insert_post_content_filter', $args['post_content'], get_current_user_id(), $post_id, 'new' );

				wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_id, 'post_name' => $post_id, 'post_content' => $args['post_content'] ) );

				if ( isset( $safe_content ) ) {
					$this->hashtagit( $post_id, $safe_content );
					update_post_meta( $post_id, '_original_content', $orig_content );
					$output['orig_content'] = stripslashes_deep( $orig_content );
				}

				if ( absint( $_POST['_wall_id'] ) > 0 ) {
					update_post_meta( $post_id, '_wall_id', absint( $_POST['_wall_id'] ) );
				}

				// Save item meta
				update_post_meta( $post_id, '_oembed', $has_oEmbed );
				update_post_meta( $post_id, '_action', 'status' );
				update_post_meta( $post_id, '_user_id', get_current_user_id() );
				update_post_meta( $post_id, '_likes', 0 );
				update_post_meta( $post_id, '_comments', 0 );

				if ( $_post_img ) {
					$photo_uri = um_is_file_owner( $_post_img, get_current_user_id() ) ? $_post_img : false;

					update_post_meta( $post_id, '_photo', $photo_uri );
					$filename = wp_basename( $photo_uri );
					$photo_metadata = get_transient( "um_{$filename}" );
					update_post_meta( $post_id, '_photo_metadata', $photo_metadata );
					delete_transient( "um_{$filename}" );

					UM()->uploader()->replace_upload_dir = true;
					UM()->uploader()->move_temporary_files( get_current_user_id(), array( '_photo' => $photo_uri ), true );
					UM()->uploader()->replace_upload_dir = false;

					$output['photo'] = $this->get_download_link( $post_id, get_current_user_id() );
					$output['photo_base'] = $photo_metadata['original_name'];

					$output['photo_orig_url'] = UM()->uploader()->get_upload_base_url() . get_current_user_id() . '/' . $filename;
					$output['photo_orig_base'] = wp_basename( $output['photo_orig_url'] );
				}

				$output['postid'] = $post_id;
				$output['content'] = $this->get_content( $post_id );
				$output['video'] = $this->get_video( $post_id );

				do_action( 'um_activity_after_wall_post_published', $post_id, get_current_user_id(), absint( $_POST['_wall_id'] ) );

			} else {

				// Updating a current wall post
				$post_id = absint( $_POST['_post_id'] );

				$output['link'] = '';

				if ( trim( $_post_content ) ) {
					$orig_content = trim( $_post_content );
					$safe_content = wp_kses( $_post_content, array(
						'br' => array()
					) );

					// shared a link
					$shared_link = $this->get_content_link( $safe_content );
					$has_oEmbed = $this->is_oEmbed( $shared_link );

					if ( isset( $shared_link ) && $shared_link && ! $_post_img && ! $has_oEmbed ) {
						$safe_content = str_replace( $shared_link, '', $safe_content );
						$output['link'] = $this->set_url_meta( $shared_link, $post_id );
					} else {
						delete_post_meta( $post_id, '_shared_link' );
					}


					$safe_content = apply_filters( 'um_activity_update_post_content_filter', $safe_content, $this->get_author( $post_id ), $post_id, 'save' );

					$args['post_content'] = $safe_content;
				}

				$args['ID'] = $post_id;
				$args = apply_filters( 'um_activity_update_post_args', $args );

//				// hash tag replies
//				$args['post_content'] = apply_filters( 'um_activity_insert_post_content_filter', $args['post_content'], get_current_user_id(), $post_id, 'new' );

				wp_update_post( $args );

				if ( isset( $safe_content ) ) {
					$this->hashtagit( $post_id, $safe_content );
					update_post_meta( $post_id, '_original_content', $orig_content );
					$output['orig_content'] = stripslashes_deep( $orig_content );
				}

				if ( trim( $_post_img ) != '' ) {

					if ( um_is_temp_file( $_post_img ) ) {
						$photo_uri = um_is_file_owner( $_post_img, get_current_user_id() ) ? $_post_img : false;

						UM()->uploader()->replace_upload_dir = true;
						UM()->uploader()->move_temporary_files( get_current_user_id(), array( '_photo' => $photo_uri ), true );
						UM()->uploader()->replace_upload_dir = false;

						update_post_meta( $post_id, '_photo', $photo_uri );
						$filename = wp_basename( $photo_uri );
						$photo_metadata = get_transient( "um_{$filename}" );
						update_post_meta( $post_id, '_photo_metadata', $photo_metadata );
						delete_transient( "um_{$filename}" );
					} else {
						$filename = wp_basename( $_post_img );
					}

					if ( ! isset( $photo_metadata ) ) {
						$photo_metadata = get_post_meta( $post_id, '_photo_metadata', $photo_metadata );
					}

					$output['photo'] = $this->get_download_link( $post_id, get_current_user_id() );
					$output['photo_base'] = $photo_metadata['original_name'];

					$output['photo_orig_url'] = UM()->uploader()->get_upload_base_url() . get_current_user_id() . '/' . $filename;
					$output['photo_orig_base'] = wp_basename( $output['photo_orig_url'] );

				} else {

					$photo_uri = get_post_meta( $post_id, '_photo', true );

					UM()->uploader()->replace_upload_dir = true;
					UM()->uploader()->delete_existing_file( $photo_uri );
					UM()->uploader()->replace_upload_dir = false;

					delete_post_meta( $post_id, '_photo' );
					delete_post_meta( $post_id, '_photo_metadata' );

					$filename = wp_basename( $photo_uri );
					delete_transient( "um_{$filename}" );

				}

				$output['postid'] = $post_id;
				$output['content'] = $this->get_content( $post_id );
				$output['video'] = $this->get_video( $post_id );

				do_action( 'um_activity_after_wall_post_updated', $post_id, get_current_user_id(), absint( $_POST['_wall_id'] ) );

			}

			// other output
			$output['permalink'] = $this->get_permalink( $post_id );
			$output['user_id'] = get_current_user_id();

			$output['has_oembed'] = $has_oEmbed;
			$output['has_text_video'] = get_post_meta( $post_id , '_video_url', true );
		}

		$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;
	}



	/**
	 * Add a new wall post
	 */
	function ajax_get_activity_post() {
		UM()->check_ajax_nonce();

		extract( $_POST );

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You can not post as guest', 'um-activity' );
		}

		if ( empty( $post_id ) ) {
			$output['error'] = __( 'You should select post first', 'um-activity' );
		}

		$post = get_post( $post_id );

		if ( empty( $post ) || is_wp_error( $post ) ) {
			$output['error'] = __( 'You should select post first', 'um-activity' );
		}

		if ( ! $output['error'] ) {
			$output['postid'] = $post_id;

			$photo_meta = get_post_meta( $post_id, '_photo_metadata', true );
			if ( ! empty( $photo_meta ) ) {
				$output['photo_base'] = $photo_meta['original_name'];
			}
			$output['orig_content'] = get_post_meta( $post_id, '_original_content', true );
			$output['photo'] = get_post_meta( $post_id, '_photo', true );
			$output['content'] = $this->get_content( $post_id );
			$output['video'] = $this->get_video( $post_id );

			// other output
			$output['permalink'] = $this->get_permalink( $post_id );
			$output['user_id'] = get_current_user_id();
			$output['has_oembed'] = get_post_meta( $post_id, '_oembed', true );
			$output['has_text_video'] = get_post_meta( $post_id , '_video_url', true );
		}

		/*$output = json_encode( $output );
		if (is_array( $output )) {
			print_r( $output );
		} else {
			echo $output;
		}
		die;*/

		wp_send_json_success( $output );
	}


	/**
	 * Get comment info
	 */
	function ajax_get_activity_comment() {
		UM()->check_ajax_nonce();

		extract( $_POST );

		$output['error'] = '';

		if ( ! is_user_logged_in() ) {
			$output['error'] = __( 'You can not post as guest', 'um-activity' );
		}

		if ( empty( $comment_id ) ) {
			$output['error'] = __( 'You should select comment first', 'um-activity' );
		}

		$comment = get_comment( $comment_id );

		if ( empty( $comment ) || is_wp_error( $comment ) ) {
			$output['error'] = __( 'You should select comment first', 'um-activity' );
		}

		if ( ! $output['error'] ) {
			$output['commentid'] = $comment_id;
			$orig_content = get_comment_meta( $comment_id, 'orig_content', true );
			$output['orig_content'] = ! empty( $orig_content ) ? $orig_content : $comment->comment_content;
			$output['content'] =  $comment->comment_content;

			// other output
			$output['permalink'] = $this->get_permalink( $comment_id );
			$output['user_id'] = get_current_user_id();
		}

		wp_send_json_success( $output );
	}


	/**
	 * @param $string
	 *
	 * @return mixed
	 */
	function allow_get_params( $string ) {
		return str_replace('&amp;', '&', $string);
	}


	/**
	 * Get Activity Posts count per page
	 * @return int
	 */
	function get_posts_per_page() {
		return UM()->mobile()->isMobile() ? UM()->options()->get( 'activity_posts_num_mob' ) : UM()->options()->get( 'activity_posts_num' );
	}


	/**
	 * Get post image URL - thumbnail, first image, first cover
	 *
	 * @param int|\WP_Post $post Optional. Post ID or WP_Post object.
	 * @return string URL
	 */
	function get_post_image_url( $post = null ) {
		$image_url = '';

		if ( has_post_thumbnail( $post ) ) {
			$image_urls = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );
			$image_url = current( $image_urls );
		} else {
			if( is_numeric( $post ) ) {
				$post = get_post( $post );
			}
			if ( is_a( $post, 'WP_Post' ) ) {
				preg_match( '/[^"]+\.(jpeg|jpg|png)/im', $post->post_content, $matches );
				if ( isset( $matches[0] ) ) {
					$image_url = esc_url_raw( $matches[ 0 ] );
				}
			}
		}

		return ( string ) $image_url;
	}
}