<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Ajax
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Ajax {


	/**
	 * User_Photos_Ajax constructor.
	 */
	function __construct() {
		// delete image
		add_action( 'wp_ajax_um_delete_album_photo', array( $this, 'um_delete_album_photo' ) );

		// update image data
		add_action( 'wp_ajax_update_um_user_photos_image', array( $this, 'update_um_user_photos_image' ) );

		// delete album
		add_action( 'wp_ajax_delete_um_user_photos_album', array( $this, 'delete_um_user_photos_album' ) );

		// update album
		add_action( 'wp_ajax_update_um_user_photos_album', array( $this, 'update_um_user_photos_album' ) );

		// create new album
		add_action( 'wp_ajax_create_um_user_photos_album', array( $this, 'create_um_user_photos_album' ) );

		// Delete all albums & photos
		add_action( 'wp_ajax_delete_my_albums_photos', array( $this, 'delete_my_albums_photos' ) );

		// download all photos
		add_action( 'wp_ajax_download_my_photos', array( $this, 'download_my_photos' ) );




		// load images
		add_action( 'wp_ajax_um_user_photos_load_more', array( $this, 'um_user_photos_load_more' ) );
		add_action( 'wp_ajax_nopriv_um_user_photos_load_more', array( $this, 'um_user_photos_load_more' ) );

		// load view with ajax
		add_action( 'wp_ajax_get_um_user_photos_view', array( $this, 'get_um_ajax_gallery_view' ) );
		add_action( 'wp_ajax_nopriv_get_um_user_photos_view', array( $this, 'get_um_ajax_gallery_view' ) );
		
		// load comments section
		add_action( 'wp_ajax_um_user_photos_get_comment_section', array( $this, 'um_user_photos_get_comment_section' ) );
		
		add_action( 'wp_ajax_nopriv_um_user_photos_get_comment_section', array( $this, 'um_user_photos_get_comment_section' ) );

		//single album
		add_action( 'wp_ajax_get_um_user_photos_single_album_view', array( $this, 'get_um_user_photos_single_album_view' ) );
		add_action( 'wp_ajax_nopriv_get_um_user_photos_single_album_view', array( $this, 'get_um_user_photos_single_album_view' ) );
		
		// like photo
		add_action( 'wp_ajax_um_user_photos_like_photo', array( $this, 'um_user_photos_like_photo' ) );

		
		// unlike photo
		add_action( 'wp_ajax_um_user_photos_unlike_photo', array( $this, 'um_user_photos_unlike_photo' ) );
		
		// update comment
		add_action( 'wp_ajax_um_user_photos_comment_update', array( $this, 'um_user_photos_comment_update' ) );
		
		// post comment
		add_action( 'wp_ajax_um_user_photos_post_comment', array( $this, 'um_user_photos_post_comment' ) );
		
		// like comment
		add_action( 'wp_ajax_um_user_photos_like_comment', array( $this, 'um_user_photos_like_comment' ) );
		
		// unlike comment
		add_action( 'wp_ajax_um_user_photos_unlike_comment', array( $this, 'um_user_photos_unlike_comment' ) );
		
		// show photo likes modal
		add_action( 'wp_ajax_get_um_user_photo_likes', array( $this, 'get_um_user_photo_likes' ) );
		
		add_action( 'wp_ajax_nopriv_get_um_user_photo_likes', array( $this, 'get_um_user_photo_likes' ) );
		
		// show photo edit comment
		add_action( 'wp_ajax_get_um_user_photos_comment_edit', array( $this, 'get_um_user_photos_comment_edit' ) );
		
		// delete comment modal
		add_action( 'wp_ajax_get_um_user_photos_comment_delete', array( $this, 'get_um_user_photos_comment_delete' ) );
		
		// delete comment
		add_action( 'wp_ajax_um_user_photos_comment_delete', array( $this, 'um_user_photos_comment_delete' ) );
		
		// show photo likes modal
		add_action( 'wp_ajax_get_um_user_photos_comment_likes', array( $this, 'get_um_user_photos_comment_likes' ) );
		
		add_action( 'wp_ajax_nopriv_get_um_user_photos_comment_likes', array( $this, 'get_um_user_photos_comment_likes' ) );

	}


	/**
	 * Load more photos with ajax
	 *
	 * @param type $res
	 * @return string
	 */
	function um_user_photos_load_more( $res = 'exit' ) {

		$profile_id = absint( $_POST['profile'] );
		$per_page = absint( $_POST['per_page'] );
		$page_no = intval( $_POST['page'] );
		$offset = $page_no * $per_page;

		$is_my_profile = false;

		if ( is_user_logged_in() && get_current_user_id() == $profile_id ) {
			$is_my_profile = true;
		}

		$photos = [];

		$latest_photos = new \WP_Query([
			'post_type' => 'attachment',
			'author__in' => [$profile_id],
			'post_status' => 'inherit',
			'posts_per_page' => $per_page,
			'offset' => $offset,
			'meta_query'    => [
				[
					'key'     => '_part_of_gallery',
					'value'   => 'yes',
					'compare' => '=',
				]
			]
		]);

		if ( $latest_photos->have_posts() ) {

			$count = $latest_photos->found_posts;

			$photos = array();
			foreach ( $latest_photos->posts as $photo ) {
				$photos[] = $photo->ID;
			}

			$args_t = compact( 'count', 'is_my_profile', 'photos', 'user_id' );
			$html = UM()->get_template( 'single-album.php', um_user_photos_plugin, $args_t );

			if ( $html ) {
				$html = preg_replace( 
						array( '/^\s+/im', '/\\r\\n/im', '/\\n/im', '/\\t+/im' ),
						array( '', ' ', ' ', ' ' ), $html );
			}

			if ( $res === 'return' ) {
				return $html;
			} else {
				exit( $html );
			}
		} else {
			if ( $res === 'return' ) {
				return 'empty';
			} else {
				exit( 'empty' );
			}
		}

		wp_reset_postdata();
	}

	
	/**
	 * Load view with ajax
	 */
	function get_um_ajax_gallery_view() {

		$view = filter_input( INPUT_POST, 'template' );
		if( empty( $view ) ) {
			exit( 'Error: no "template" input.' );
		} else {
			$template = "$view.php";
		}

		if ( $view === 'gallery' ) {
			echo UM()->Photos_API()->shortcodes()->get_gallery_content();
			exit;
		}

		$album_id = filter_input( INPUT_POST, 'album_id' );
		if ( $album_id ) {
			$album = get_post( $album_id );
		}

		$image_id = filter_input( INPUT_POST, 'image_id' );
		if ( $image_id ) {
			$photo = get_post( $image_id );
		}

		$user_id = filter_input( INPUT_POST, 'user_id' );
		if ( empty( $user_id ) ) {
			$user_id = um_user( 'ID' );
		}

		$is_my_profile = is_user_logged_in() && get_current_user_id() == $user_id;

		$args_t = compact( 'album', 'is_my_profile', 'photo', 'user_id', 'view' );
		$html = UM()->get_template( $template, um_user_photos_plugin, $args_t );

		if ( $html ) {
			$html = preg_replace(
					array( '/^\s+/im', '/\\r\\n/im', '/\\n/im', '/\\t+/im' ),
					array( '', ' ', ' ', ' ' ), $html );
		}

		exit( $html );
	}


	/**
	 * Single album loading
	 *
	 * @param string $res - 'exit' or 'return'
	 * @return string - HTML
	 */
	function get_um_user_photos_single_album_view( $res = 'exit' ) {

		$album_id = filter_input( INPUT_POST, 'id' );
		if ( empty( $album_id ) ) {
			if ( $res === 'return' ) {
				return '';
			} else {
				exit;
			}
		}

		$album = get_post( $album_id );
		$photos = get_post_meta( $album_id, '_photos', true );
		$is_my_profile = is_user_logged_in() && get_current_user_id() == $album->post_author;

		$args_t = compact( 'album', 'album_id', 'is_my_profile', 'photos' );

		$html = UM()->get_template( 'album-head.php', um_user_photos_plugin, $args_t );
		$html .= UM()->get_template( 'single-album.php', um_user_photos_plugin, $args_t );

		if( $html ) {
			$html = preg_replace(
					array( '/^\s+/im', '/\\r\\n/im', '/\\n/im', '/\\t+/im' ),
					array( '', ' ', ' ', ' ' ), $html );
		}

		if( $res === 'return' ) {
			return $html;
		} else {
			exit( $html );
		}
	}
	

	/**
	 * Create new album
	 */
	function create_um_user_photos_album() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_add_album' ) ) {
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}

		/*validation*/
		$error = false;
		$response = [
			'type' => 'error',
			'messages' => []
		];

		$allowed = [
			'image/jpeg',
			'image/png',
			'image/jpg',
			'image/gif',
		];

		if ( ! isset( $_POST['title'] ) || sanitize_text_field( $_POST['title'] ) == '' ) {
			$error = true;
			$response['messages'][] = __('Album title is required','um-user-photos');
		}

		if ( isset( $_FILES['album_cover']['tmp_name'] ) && $_FILES['album_cover']['tmp_name'] !='' ) {

			if ( ! in_array( $_FILES['album_cover']['type'], $allowed ) ) {
				$error = true;
				$response['messages'][] = $_FILES['album_cover']['type'] . ' ' . __( 'files are not allowed', 'um-user-photos' );
			}

			um_maybe_unset_time_limit();

			add_filter( 'wp_handle_upload_prefilter', array( $this, 'validate_upload' ) );
		}

		if ( ! is_user_logged_in() ) {
			$error = true;
			$response['messages'][] = __( 'Invalid request', 'um-user-photos' );
		}

		if ( $error ) {
			echo json_encode( $response );
			die;
		}

		/*end validation*/
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$title = sanitize_text_field( $_POST['title'] );

		$post_id = wp_insert_post([
			'post_type' => 'um_user_photos',
			'post_title' => $title,
			'post_author' => get_current_user_id(),
			'post_status' => 'publish'
		]);

		$photos = [];

		if(isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] !=''){

			$uploadedfile = $_FILES['album_cover'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if($movefile && ! isset($movefile['error'])){

				$wp_filetype = $movefile['type'];
				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $wp_filetype,
					'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content' => '',
					'post_parent' => $post_id,
					'post_author' => get_current_user_id(),
					'post_status' => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $filename);
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta( $attach_id,'_part_of_gallery','yes');
				update_post_meta($post_id,'_thumbnail_id',$attach_id);

			}else{
				$response = ['type' => 'error','messages' => [$movefile['error']]];
				echo json_encode($response);
				die;
			}
		}


		if(isset($_FILES['album_images']) && count($_FILES['album_images'])){

			$gallery_images = $_FILES['album_images'];
			$count_images = count($_FILES['album_images']['name']);
			for($i=0;$i<$count_images;$i++){

				if(! isset($_FILES['album_images']['tmp_name'][$i]) || trim($_FILES['album_images']['tmp_name'][$i]) == ''){
					continue;
				}

				$uploadedfile = [
					'name' => $_FILES['album_images']['name'][$i],
					'type' => $_FILES['album_images']['type'][$i],
					'tmp_name' => $_FILES['album_images']['tmp_name'][$i],
					'error' => $_FILES['album_images']['error'][$i],
					'size' => $_FILES['album_images']['size'][$i]
				];

				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

				if($movefile && ! isset( $movefile['error'] )){

					$wp_filetype = $movefile['type'];
					$filename = $movefile['file'];
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
						'post_mime_type' => $wp_filetype,
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_parent' => $post_id,
						'post_author' => get_current_user_id(),
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $filename);
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					update_post_meta( $attach_id,'_part_of_gallery','yes');

					$photos[] = $attach_id;
				}
				else{
					$response = ['type' => 'error','messages' => [$movefile['error']]];
					echo json_encode($response);
					die;
				}

			}

		}

		update_post_meta($post_id,'_photos',$photos);

		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_created',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_album_created',$post_id);

		echo 'success';
		die;
	}


	/**
	 * Update album
	 */
	function update_um_user_photos_album() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_edit_album' ) ) {
			$response = array(
				'type'      => 'error',
				'messages'  => []
			);
			$response['messages'][] = __( 'Invalid nonce', 'um-user-photos' );
			echo json_encode( $response);
			die;
		}

		/*validation*/

		$error = false;
		$response = [
			'type'      => 'error',
			'messages'  => []
		];

		if ( ! isset( $_POST['album_id'] ) || ! is_numeric( sanitize_key( $_POST['album_id'] ) ) ){
			$error = true;
			$response['messages'][] = 'Invalid album';
		}

		if ( ! isset( $_POST['album_title'] ) || sanitize_text_field( $_POST['album_title'] ) == '') {
			$error = true;
			$response['messages'][] = __( 'Album title is required', 'um-user-photos' );
		}


		$album = get_post( absint( $_POST['album_id'] ) );
		if ( $album && is_user_logged_in() ) {

			if ( $album->post_author != get_current_user_id() ) {
				$error = true;
				$response['messages'][] = __( 'Invalid request', 'um-user-photos' );
			}

		} else {
			$error = true;
			$response['messages'][] = __( 'Invalid request', 'um-user-photos' );
		}

		if ( $error ) {
			echo json_encode($response);
			die;
		}

		um_maybe_unset_time_limit();

		add_filter( "wp_handle_upload_prefilter", array( $this, "validate_upload" ) );

		/*end validation*/

		$post_id = wp_update_post([
			'ID'            => absint( $_POST['album_id'] ),
			'post_title'    => sanitize_text_field( $_POST['album_title'] )
		]);

		$photos = [];
		if ( isset( $_POST['photos'] ) && is_array( $_POST['photos'] ) && ! empty( $_POST['photos'] ) ) {
			$photos = $_POST['photos'];
		}

		if ( isset( $_FILES['album_cover']['tmp_name'] ) && sanitize_text_field( $_FILES['album_cover']['tmp_name'] ) != '' ) {
			$uploadedfile = $_FILES['album_cover'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

			if ( $movefile && ! isset( $movefile['error'] ) ) {

				$wp_filetype = $movefile['type'];
				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $wp_filetype,
					'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content' => '',
					'post_parent' => absint( $_POST['album_id'] ),
					'post_author' => get_current_user_id(),
					'post_status' => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $filename);
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta( $attach_id,'_part_of_gallery','yes');

				update_post_meta( absint( $_POST['album_id'] ), '_thumbnail_id', $attach_id );
			} else {
				$response = ['type' => 'error','messages' => [$movefile['error']]];
				echo json_encode($response);
				die;
			}
		}

		// upload more photos and add to $photos array
		if(isset($_FILES['album_images']) && count($_FILES['album_images'])){

			$gallery_images = $_FILES['album_images'];
			$count_images = count($_FILES['album_images']['name']);
			for($i=0;$i<$count_images;$i++){

				if(! isset($_FILES['album_images']['tmp_name'][$i]) || trim($_FILES['album_images']['tmp_name'][$i]) == ''){
					continue;
				}

				$uploadedfile = [
					'name' => $_FILES['album_images']['name'][$i],
					'type' => $_FILES['album_images']['type'][$i],
					'tmp_name' => $_FILES['album_images']['tmp_name'][$i],
					'error' => $_FILES['album_images']['error'][$i],
					'size' => $_FILES['album_images']['size'][$i]
				];

				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

				if($movefile && ! isset( $movefile['error'] )){

					$wp_filetype = $movefile['type'];
					$filename = $movefile['file'];
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
						'post_mime_type' => $wp_filetype,
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_parent' => $post_id,
						'post_author' => get_current_user_id(),
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $filename);
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					update_post_meta( $attach_id,'_part_of_gallery','yes');

					$photos[] = $attach_id;
				} else {
					$response = ['type' => 'error','messages' => [$movefile['error']]];
					echo json_encode($response);
					die;
				}

			}

		}

		if ( is_array( $photos ) && ! empty( $photos ) ) {
			update_post_meta( absint( $_POST['album_id'] ), '_photos', $photos );
		} else {
			delete_post_meta( absint( $_POST['album_id'] ), '_photos' );
		}

		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_updated',function($post_id){
			// custom code
		});
		*/
		do_action( 'um_user_photos_after_album_updated', absint( $_POST['album_id'] ) );

		echo 'success';
		die;
	}


	/**
	 * delete album
	 */
	function delete_um_user_photos_album() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_delete_album' ) ) {
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __( 'Invalid nonce', 'um-user-photos' );
			echo json_encode($response);
			die;
		}


		$error = false;
		$response = [
			'type' => 'error',
			'messages' => []
		];
		$id = absint( $_POST['id'] );
		$album = get_post($id);

		if(! $album){
			$error = true;
			$response['messages'][] = __('Invalid request','um-user-photos');
		}
		else{
			if(! is_user_logged_in() || $album->post_author != get_current_user_id()){
				$error = true;
				$response['messages'][] = __('Invalid request','um-user-photos');
			}
		}

		if($error){
			echo json_encode($response);
			die;
		}

		$photos = get_post_meta($id,'_photos',true);
		$wall_photo = get_post_meta($id,'_thumbnail_id',true);

		/*
		@param $post_id (int)
		add_action('um_user_photos_before_album_deleted',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_before_album_deleted',$id);

		if(is_array($photos) && ! empty($photos)){
			for($i=0;$i<count($photos);$i++){
				wp_delete_attachment($photos[$i],true);
			}
		}
		if($wall_photo){
			wp_delete_attachment($wall_photo,true);
		}

		wp_delete_post($id,true);

		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_deleted',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_album_deleted',$id);

		echo 'success';
		die;
	}


	/**
	 * update image
	 */
	function update_um_user_photos_image() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_edit_image' ) ) {
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __( 'Invalid nonce', 'um-user-photos' );
			echo json_encode($response);
			die;
		}

		$error = false;
		$response = [];

		$id = absint( $_POST['id'] );

		if ( ! $id || ! is_numeric( $id ) ) {
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		$image = get_post( $id );
		if(! $image || $image->post_author != get_current_user_id()){
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		if ( ! isset( $_POST['title'] ) || sanitize_text_field( $_POST['title'] ) =='' ) {
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Title is required','um-user-photos');
		}

		if($error){
			echo json_encode($response);
			die;
		}

		wp_update_post([
			'ID' => intval($id),
			'post_title' => sanitize_text_field( $_POST['title'] ),
			'post_excerpt' => sanitize_text_field( $_POST['caption'] )
		]);
		
		if ( isset( $_POST['disable_comments'] ) ) {
			update_post_meta( $id, '_disable_comment', true);
		} else {
			delete_post_meta( $id, '_disable_comment');
		}

		/*
		@param $attachment_id (int)
		add_action('um_user_photos_after_photo_updated',function($attachment_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_photo_updated',$id);

		$success_text = __('Update successfull','um-user-photos');
		echo json_encode([
			'type' => 'success',
			'messages' => [$success_text]
		]);
		die;

	}


	/**
	 * Delete image
	 */
	function um_delete_album_photo() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_delete_photo' ) ) {
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __( 'Invalid nonce', 'um-user-photos' );
			echo json_encode($response);
			die;
		}



		$image_id = absint( $_POST['image_id'] );
		$album_id = absint( $_POST['album_id'] );

		/*
		@param $attachment_id (int)
		@param $album_id (int)
		add_action('um_user_photos_before_photo_delete',function($attachment_id,$album_id){
			// custom code
		});
		*/
		do_action('um_user_photos_before_photo_delete',$image_id,$album_id);

		$album = get_post($album_id);
		$image = get_post($image_id);

		$user_id = 0;

		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		if(! $user_id || ! $image || ! $album){
			echo 'Invalid request';
			die;
		}
		elseif($image->post_author != $user_id || $album->post_author != $user_id){
			echo 'Invalid request';
			die;
		}
		else{

			/*
				@param $attachment_id (int)
				@param $album_id (int)
				add_action('um_user_photos_before_photo_deleted',function($image_id,$album_id){
					// custom code
				});
			*/

			do_action('um_user_photos_before_photo_deleted',$image_id,$album_id);


			wp_delete_attachment($image_id,true);

			/*
				@param $attachment_id (int)
				@param $album_id (int)
				add_action('um_user_photos_after_photo_deleted',function($image_id,$album_id){
					// custom code
				});
			*/
			do_action('um_user_photos_after_photo_deleted',$image_id,$album_id);

			echo 'success';
			die;
		}

	}
	

	/**
	 * Delete my all albums & photos
	 * @hook 'wp_ajax_delete_my_albums_photos'
	 */
	function delete_my_albums_photos() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_user_photos_delete_all' ) ) {
			$response = [
				'type' => 'error',
				'messages' => array( __('Invalid nonce','um-user-photos') )
			];
			wp_send_json_error( $response );
			exit;
		}

		if (! is_user_logged_in()){
			$response = [
				'type' => 'error',
				'messages' => array( __('Invalid request','um-user-photos') )
			];
			wp_send_json_error( $response );
			exit;
		}

		$profile = absint( $_POST['profile_id'] );
		$user_id = get_current_user_id();

		if ( $profile !== $user_id ) {
			return;
		}

		/* Remove photos */
		$photos = new \WP_Query( [
				'post_type'			 => 'attachment',
				'author__in'		 => [ $user_id ],
				'post_status'		 => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'meta_query'		 => [
						[
								'key'			 => '_part_of_gallery',
								'value'		 => 'yes',
								'compare'	 => '=',
						]
				]
		] );
		if ( $photos->have_posts() ):
			while ( $photos->have_posts() ):
				$photos->the_post();
				wp_delete_attachment( get_the_ID(), true );
			endwhile;
		endif;
		wp_reset_postdata();

		/* Remove albums */
		$albums = new \WP_Query( [
				'post_type'			 => 'um_user_photos',
				'author__in'		 => [ $user_id ],
				'posts_per_page' => -1
		] );
		if ( $albums->have_posts() ):
			while ( $albums->have_posts() ):
				$albums->the_post();
				wp_delete_post( get_the_ID(), true );
			endwhile;
		endif; // has albums
		wp_reset_postdata();

		/*
		 * @param $user_id (int)
		 * @example
			add_action('um_user_photos_after_user_albums_deleted',function($user_id){
				// custom code
			});
		*/
		do_action('um_user_photos_after_user_albums_deleted',$user_id);

		wp_send_json_success( array(
				'albums' => $albums->found_posts,
				'photos' => $photos->found_posts
		) );
		exit;
	}


	/**
	 * Download all photos
	 */
	function download_my_photos() {

		$profile = absint( $_REQUEST['profile_id'] );
		$user_id = get_current_user_id();
		$notice = '';

		if ( ! is_user_logged_in() ) {
			$notice = __( 'Invalid request', 'um-user-photos' );
		}

		if ( ! class_exists( '\ZipArchive' ) ) {
			$notice = __( 'Your download could not be created. It looks like you do not have ZipArchive installed on your server.', 'um-user-photos' );
		}

		if ( empty( $notice ) && $profile == $user_id ) {

			$photos = new \WP_Query( array(
				'post_type' => 'attachment',
				'author__in' => [$user_id],
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'meta_query'    => array(
					array(
						'key'     => '_part_of_gallery',
						'value'   => 'yes',
						'compare' => '=',
					)
				),
			) );

			if ( $photos->have_posts() ) {

				$zip         = new \ZipArchive();
				$zip_name    = time() . '.zip';
				$uploads_dir = WP_CONTENT_DIR . '/uploads/user-photos';
				if ( ! is_dir( $uploads_dir ) ) {
					mkdir( $uploads_dir );
				}
				$new_zip    = $uploads_dir . '/' . $zip_name;
				$zip_opened = $zip->open( $new_zip, \ZipArchive::CREATE );

				while ( $photos->have_posts() ) {
					$photos->the_post();
					$file_path  = get_attached_file( get_the_ID(), true );
					$file_type  = $filetype = wp_check_filetype( $file_path );
					$ext        = $file_type['ext'];
					$image_name = get_the_title() . '.' . $ext;
					if ( file_exists( $file_path ) ) {
						$zip->addFile( $file_path, $image_name );
					}
				}

				$zip->close();
				header( "Content-type:application/zip" );
				header( 'Content-Disposition: attachment; filename=' . $zip_name );
				readfile( $new_zip );
				unlink( $new_zip );
				die;
			} else {
				$notice = __( 'Nothing to download', 'um-user-photos' );
			}

			wp_reset_postdata();
		}

		if ( $notice ) {
			update_user_meta( $user_id, 'um_download_my_photos_notice', $notice );
			$location = $_SERVER['HTTP_REFERER'];
			wp_safe_redirect( $location );
		}
	}

	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function validate_upload( $file ) {

		$error = $this->validate_image_data( $file['tmp_name'] );

		if ( $error ) {
			$file['error'] = $error;
		}

		return $file;
	}


	/**
	 * Check image upload and handle errors
	 *
	 * @param $file
	 *
	 * @return null|string
	 */
	public function validate_image_data( $file ) {
		$error = null;

		if ( ! function_exists( 'wp_get_image_editor' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		$image = wp_get_image_editor( $file );
		if ( is_wp_error( $image ) ) {
			return __( 'Your image is invalid!', 'um-user-photos' );
		}

		$image_sizes = $image->get_size();
		$image_info['width'] = $image_sizes['width'];
		$image_info['height'] = $image_sizes['height'];
		$image_info['ratio'] = $image_sizes['width'] / $image_sizes['height'];

		$image_info['quality'] = $image->get_quality();

		$image_type = wp_check_filetype( $file );
		$image_info['extension'] = $image_type['ext'];
		$image_info['mime']= $image_type['type'];
		$image_info['size'] = filesize( $file );

		if ( isset( $image_info['invalid_image'] ) && $image_info['invalid_image'] == true ) {
			$error = __( 'Your image is invalid or too large!', 'um-user-photos' );
		}

		return $error;
	}


	/**
	 *
	 */
	function um_user_photos_get_comment_section() {
		$image_id = absint( $_POST['image_id'] );

		$content = UM()->get_template( 'caption.php', um_user_photos_plugin, array( 'image_id' => $image_id ) );

		wp_send_json_success( $content );
	}


	/**
	 * like photo
	 */
	function um_user_photos_like_photo() {
		
		$output['error'] = '';

		if ( ! is_user_logged_in() )
			$output['error'] = __( 'You must login to like', 'um-user-photos' );

		if ( ! isset( $_POST['postid'] ) || ! is_numeric( sanitize_key( $_POST['postid'] ) ) )
			$output['error'] = __( 'Invalid photo', 'um-activity' );

		if ( ! $output['error'] ) {

			$liked = get_post_meta( absint( $_POST['postid'] ), '_liked', true );
			
			if ( ! $liked ) {
				
				$liked = array( get_current_user_id() );
				
			} else {
				
				$liked[] = get_current_user_id();
			}
			
			update_post_meta( absint( $_POST['postid'] ), '_liked', $liked );
			
			wp_send_json_success( count( $liked ) );
		
		}
	} // um_user_photos_like_photo
	
	
	
	// unlike photo
	function um_user_photos_unlike_photo(){
		
		$output['error'] = '';

		if ( ! is_user_logged_in())
			$output['error'] = __( 'You must login to like', 'um-user-photos' );

		if ( ! isset( $_POST['postid'] ) || ! is_numeric( sanitize_key( $_POST['postid'] ) ) )
			$output['error'] = __( 'Invalid photo', 'um-activity' );

		if ( ! $output['error'] ) {

			$liked = get_post_meta( absint( $_POST['postid'] ), '_liked', true );

			if ( $liked ) {
				
				$liked = array( get_current_user_id() );
				if ( ( $key = array_search( get_current_user_id(), $liked ) ) !== false ) {
					unset( $liked[$key] );
				}
				
			} else {
				$liked = [];
			}
			
			update_post_meta( absint( $_POST['postid'] ), '_liked', $liked );
			
			wp_send_json_success( count( $liked ) );
		
		}
	} // um_user_photos_like_photo
	
	
	
	// unlike photo
	function um_user_photos_post_comment(){
		
		$output['error'] = [];

		if ( ! is_user_logged_in() ) {
			$output['error'][] = __( 'Login to post a comment', 'um-user-photos' );
		}

		if ( ! isset( $_POST['image_id'] ) || ! is_numeric( sanitize_key( $_POST['image_id'] ) ) ) {
			$output['error'][] = __( 'Invalid wall post', 'um-um-user-photos' );
		}

		if ( ! isset( $_POST['comment'] ) || sanitize_text_field( $_POST['comment'] ) == '' ) {
			$output['error'][] = __( 'Enter a comment first', 'um-um-user-photos' );
		}
		
		if( ! empty( $output['error'] ) ){
			wp_send_json_error($output);
		}
		
		
		um_fetch_user( get_current_user_id() );

		if ( isset( $_POST['image_id'] ) ) {
			$post_id = absint( $_POST['image_id'] );
		}

		$orig_content = sanitize_text_field( $_POST['comment'] );
		$comment_content = wp_kses( $_POST['comment'], array(
				'br' => array()
			));
		
		$time = current_time( 'mysql' );
		$data = array(
			'comment_post_ID'      => $post_id,
			'comment_author'       => um_user( 'display_name' ),
			'comment_author_email' => um_user( 'user_email' ),
			'comment_author_url'   => um_user_profile_url(),
			'comment_content'      => $comment_content,
			'user_id'              => get_current_user_id(),
			'comment_approved'     => 1,
			'comment_author_IP'    => um_user_ip(),
			'comment_type'         => 'um-user-photos',
			'comment_date'		   => $time
		);
		
		$commentid = wp_insert_comment( $data );
		
		wp_update_comment_count_now( $post_id );
		
		ob_start();

		UM()->get_template( 'comment.php', um_user_photos_plugin, array(
			'user_id'   => um_user('ID'),
			'content'   => $comment_content,
			'date'      => $time,
			'id'        => $commentid,
			'image_id'  => $post_id,
		), true );

		$content = ob_get_clean();

		$image = get_post( $post_id );
		$comment_count = $image->comment_count;
		wp_send_json_success([
			'content' => $content,
			'count' => $comment_count
		]);
		
	} // um_user_photos_post_comment
	
	
	
	
	
	
	// unlike comment
	function um_user_photos_like_comment(){
		
		$likes = get_comment_meta( absint( $_POST['commentid'] ), '_likes', true );

		if ( $likes && ! in_array( get_current_user_id(), $likes ) ) {

			$likes[] = get_current_user_id();


		} else {
			$likes = [get_current_user_id()];
		}

		update_comment_meta( absint( $_POST['commentid'] ), '_likes', $likes );

		wp_send_json_success([
			'count' => count( $likes ),
			'user_id' => get_current_user_id()
		]);
	}
	
	
	// like comment
	function um_user_photos_unlike_comment() {
		$likes = get_comment_meta( absint( $_POST['commentid'] ), '_likes', true );

		if ( $likes && in_array( get_current_user_id(), $likes ) ) {

			if ( ( $key = array_search( get_current_user_id(), $likes ) ) !== false ) {
				unset( $likes[ $key ] );
			}


		} else {

			$likes = [];

		}

		update_comment_meta( absint( $_POST['commentid'] ), '_likes', $likes );

		wp_send_json_success([
			'count' => count( $likes ),
			'user_id' => get_current_user_id()
		]);
	}


	/**
	 * show photo likes
	 */
	function get_um_user_photo_likes(){
		
		$likes = get_post_meta( absint( $_POST['image_id'] ), '_liked', true );

		ob_start();

		UM()->get_template( 'modal/likes.php', um_user_photos_plugin, array(
			'likes' => $likes,
		), true );

		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}

	
	// show photo likes
	function get_um_user_photos_comment_likes(){
		$likes = get_comment_meta( absint( $_POST['comment_id'] ), '_likes', true );

		ob_start();

		UM()->get_template( 'modal/likes.php', um_user_photos_plugin, array(
			'likes' => $likes,
		), true );

		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}


	/**
	 * show edit comment
	 */
	function get_um_user_photos_comment_edit(){
		
		$comment = get_comment(absint( $_POST['comment_id'] ) );

		ob_start();

		UM()->get_template( 'modal/edit-comment.php', um_user_photos_plugin, array(
			'comment' => $comment,
		), true );

		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}
	
	// delete comment modal
	function get_um_user_photos_comment_delete(){
		$comment = get_comment( absint( $_POST['comment_id'] ) );

		ob_start();

		UM()->get_template( 'modal/delete-comment.php', um_user_photos_plugin, array(
			'comment' => $comment,
			'message' => sanitize_text_field( $_POST['msg'] ),
		), true );

		$content = ob_get_clean();

		wp_send_json_success( array( 'content' => $content ) );
	}
	
	// delete comment
	function um_user_photos_comment_delete() {
		$deleted = wp_delete_comment( absint( $_POST['comment_id'] ), true );
		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( [ 'message' => 'Could not be deleted.' ] );
		}
	}
	
	
	
	function um_user_photos_comment_update() {
		
		$updated = wp_update_comment([
			'comment_ID'        => absint( $_POST['comment_id'] ),
			'comment_content'   => sanitize_textarea_field( $_POST['comment_content'] ),
		]);
		
		
		if( $updated ):
		
		wp_send_json_success([
				'message' => '<p style="background: green;color: #fff;text-align: center;
    line-height: 40px;border-radius: 5px;">'.__('Comment updated','um-user-photos').'</p>',
				'comment' => sanitize_textarea_field( $_POST['comment_content'] ),
				'comment_id' => absint( $_POST['comment_id'] ),
			]);
		
		else:
		
			wp_send_json_error([
				'message' => 'Could not update'
			]);
		
		endif;
	}

}