<?php

namespace um_ext\um_instagram\core;

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Instagram_Public
 * @package um_ext\um_instagram\core
 *
 * @since      1.0.0
 */
class Instagram_Public {

	/**
	 * @var array Instagram photos
	 */
	public $photos = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		//locale
		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ) );

		// Assets
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

		add_action( 'um_after_user_updated', array( &$this, 'user_after_updating_profile' ), 10, 3 );
		add_filter( 'um_edit_field_profile_instagram_photo', array( &$this, 'edit_field_profile_instagram_photo' ), 9.120, 2 );
		add_filter( 'um_view_field_value_instagram_photo', array( &$this, 'view_field_profile_instagram_photo' ), 10, 2 );
		add_filter( 'body_class', array( &$this, 'body_class' ), 999, 1 );

		add_action( 'wp_ajax_um_instagram_get_photos', array( $this, 'ajax_get_photos' ) );
		add_action( 'wp_ajax_nopriv_um_instagram_get_photos', array( $this, 'ajax_get_photos' ) );
	}

	/**
	 * Get Instagram photos via Ajax
	 * @since  1.0.0
	 */
	public function ajax_get_photos() {
		UM()->check_ajax_nonce();

		if ( !$this->is_enabled() ) {
			wp_send_json_error();
		}

		$data = array_merge( array(
				'action'		 => 'um_instagram_get_photos',
				'metakey'		 => 'ig_gallery',
				'viewing'		 => 'true',
				'um_user_id' => get_current_user_id()
				), $_REQUEST );

		$access_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'], $data['um_user_id'] );
		$response = array();

		if ( $access_token ) {
			$photos = $this->get_user_photos( $access_token, $data['viewing'] );
			if ( !empty( $photos ) ) {
				$response['photos'] = $photos;
				$response['has_photos'] = true;
				$response['has_error'] = false;
			} else {
				$response['photos'] = '';
				$response['has_photos'] = false;
				$response['has_error'] = true;
				$response['error_code'] = 'no_photos_found';
			}
		} else {
			$response['has_error'] = true;
			$response['photos'] = '';
			$response['error_code'] = 'no_access_token';
		}

		$response['raw_request'] = $_REQUEST;

		return wp_send_json( $response );
	}

	/**
	 * Add body class
	 * @param  array $classes
	 * @return array
	 *
	 * @since  1.0.0
	 */
	public function body_class( $classes ) {

		if ( !$this->is_enabled() ) {
			return $classes;
		}

		if ( um_is_core_page( 'user' ) ) {
			$classes[] = 'um-profile-id-' . um_get_requested_user();
		}

		return $classes;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( $this->is_enabled() ) {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

			wp_register_script( 'um_instagram', um_instagram_url . 'assets/js/um-instagram' . $suffix . '.js', array( 'jquery', 'wp-util', 'um_scripts' ), um_instagram_version, true );
			$translation_array = array(
					'image_loader' => um_url . '/assets/img/loading-dots.gif',
			);
			wp_localize_script( 'um_instagram', 'um_instagram', $translation_array );

			wp_register_style( 'um_instagram', um_instagram_url . 'assets/css/um-instagram' . $suffix . '.css', array(), um_instagram_version );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_instagram_textdomain, WP_LANG_DIR . '/plugins/' . um_instagram_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_instagram_textdomain, false, um_instagram_path . '/languages/' );
	}

	/**
	 * @return string
	 */
	public function nav_template() {
		if ( !$this->is_enabled() ) {
			return '';
		}

		wp_enqueue_script( 'um_instagram' );
		wp_enqueue_style( 'um_instagram' );

		$output = UM()->get_template( 'nav.php', um_instagram_plugin );
		return $output;
	}

	/**
	 * Customize instagram photo field in profile edit
	 * filter hook: um_edit_field_profile_instagram_photo
	 *
	 * @since 1.0.0
	 * @param string $output
	 * @param array $data
	 * @return string
	 */
	public function edit_field_profile_instagram_photo( $output, $data ) {

		if ( ! $this->is_enabled() ) {
			return '';
		}

		$user_id = um_profile_id();

		$error_message = get_user_meta( $user_id, 'um_instagram_error_message', true );
		if ( $error_message ) {
			update_user_meta( $user_id, 'um_instagram_error_message', '' );
		}
		
		$has_token = get_user_meta( $user_id, 'um_instagram_token_new', true );
		if ( $has_token ) {
			update_user_meta( $user_id, 'um_instagram_token_new', '' );
		} else {
			$has_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'] );
		}

		if ( ! $has_token ) {
			$hide = false;
			$version = UM()->options()->get( 'instagram_photo_api_version' );
			if ( $version == 'instagram' ) {

				$client_id = UM()->options()->get( 'instagram_photo_client_id' );
				$client_secret = UM()->options()->get( 'instagram_photo_client_secret' );

				if ( empty( $client_id ) || empty( $client_secret ) ) {
					$hide = true;
				}
			} elseif ( $version == 'facebook' ) {

				$app_id = UM()->options()->get( 'instagram_photo_app_id' );
				$app_secret = UM()->options()->get( 'instagram_photo_app_secret' );

				if ( empty( $app_id ) || empty( $app_secret ) ) {
					$hide = true;
				}
			}

			if ( $hide ) {
				return '';
			}
		}

		wp_enqueue_script( 'um_instagram' );
		wp_enqueue_style( 'um_instagram' );

		$t_args = compact( 'data', 'error_message', 'has_token' );
		$output = UM()->get_template( 'field-edit.php', um_instagram_plugin, $t_args );

		return $output;
	}

	/**
	 * Customize instagram photo in profile view
	 * @param  string $output
	 * @param  array $data
	 * @return string
	 */
	public function view_field_profile_instagram_photo( $output, $data ) {
		if ( !$this->is_enabled() ) {
			add_filter( 'um_instagram_photo_form_show_field', array( &$this, 'instagram_photo_form_show_field' ), 99, 2 );
			return $output;
		}

		$has_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'] );
		if ( !$has_token ) {
			return $output;
		}

		wp_enqueue_script( 'um_instagram' );
		wp_enqueue_style( 'um_instagram' );

		$t_args = compact( 'data', 'has_token' );
		$output = UM()->get_template( 'field-view.php', um_instagram_plugin, $t_args );

		return $output;
	}

	/**
	 * Get user Instagram photos
	 *
	 * @param string $access_token
	 * @param bool $viewing
	 * @return string
	 */
	public function get_user_photos( $access_token, $viewing = true ) {
		if ( !$this->is_enabled() ) {
			return '';
		}

		$offset = intval( filter_input( INPUT_POST, 'offset', FILTER_SANITIZE_NUMBER_INT ) );
		$photos = get_transient( 'um_instagram_photos_' . $access_token );

		if ( empty( $photos ) ) {

			$api_version = trim( UM()->options()->get( 'instagram_photo_api_version' ) );
			switch ( $api_version ) {
				case 'facebook':

					$instagram = UM()->Instagram_API()->connect()->call_API();
					$instagram->setAccessToken( $access_token );
					$response = $instagram->getUserMedia();

					if ( is_a( $response, 'Exception' ) || is_a( $response, 'Error' ) ) {
						return $response->getMessage();
					}

					if ( !empty( $response->data ) ) {

						$photos = array();
						foreach ( $response->data as $photo ) {
							if ( $photo->media_type !== 'IMAGE' ) {
								continue;
							}
							$photo->images = (object) array(
											'standard_resolution'	 => (object) array(
													'url' => $photo->media_url
											),
											'thumbnail'						 => (object) array(
													'url' => $photo->media_url
											)
							);
							$photos[] = $photo;
						}
					}
					break;

				case 'instagram':
				default:
					$response = wp_remote_get( 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $access_token . '&count=18' );
					if ( empty( $response['body'] ) ) {
						return '';
					}

					$photosdata = json_decode( $response['body'] );
					if ( isset( $photosdata->data ) ) {
						$photos = $photosdata->data;
					}
					break;
			}

			if ( !empty( $photos ) ) {
				set_transient( 'um_instagram_photos_' . $access_token, $photos, 300 );
				update_user_meta( get_current_user_id(), 'um_instagram_photos', $photos );
			}
		}

		if ( empty( $photos ) ) {
			$photos = get_user_meta( get_current_user_id(), 'um_instagram_photos', true );
		}

		if ( empty( $photos ) ) {
			return '';
		}

		// Return only data
		$dataType = filter_input(INPUT_POST, 'dataType', FILTER_SANITIZE_STRING);
		if( $dataType === 'json' ){
			return $photos;
		}

		$photos_count = count( $photos );
		$this->photos = $photos = array_slice( $photos, $offset, 6 );

		wp_enqueue_script( 'um_instagram' );
		wp_enqueue_style( 'um_instagram' );

		$t_args = compact( 'offset', 'photos', 'photos_count', 'viewing' );
		$output = UM()->get_template( 'user-photos.php', um_instagram_plugin, $t_args );

		return $output;
	}

	/**
	 * Get instagram user details
	 * @param  string $access_token
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function get_user_details( $access_token ) {
		if ( !$this->is_enabled() ) {
			return '';
		}

		$error = null;
		$api_version = trim( UM()->options()->get( 'instagram_photo_api_version' ) );


		switch ( $api_version ) {
			case 'facebook':

				$instagram = UM()->Instagram_API()->connect()->call_API();
				$instagram->setAccessToken( $access_token );
				$response = $instagram->getUser();

				if ( is_a( $response, 'Exception' ) || is_a( $response, 'Error' ) ) {
					return $response->getMessage();
				}

				if ( !empty( $response ) ) {
					$user = (object) $response;

					if ( isset($user->error) && isset($user->error->message) ) {
						$error = $user->error->message;
					}
				}
				break;

			case 'instagram':
			default:
				$response = wp_remote_get( 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token );
				if ( empty( $response['body'] ) && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
					error_log( 'UM Instagram Error: get_user_details()' );
					error_log( 'UM Instagram: RESPONSE => ' . json_encode( $response ) );
				}

				$userdata = json_decode( $response['body'] );
				if ( isset( $userdata->data ) ) {
					$user = (object) $userdata->data;
				}
				if ( isset($userdata->meta) && isset($userdata->meta->error_message) ) {
					$error = $userdata->meta->error_message;
				}
				break;
		}

		if ( empty( $error ) && empty( $user )  ) {
			return '';
		}

		wp_enqueue_script( 'um_instagram' );
		wp_enqueue_style( 'um_instagram' );

		$t_args = compact( 'error', 'user' );
		$output = UM()->get_template( 'user-details.php', um_instagram_plugin, $t_args );

		return $output;
	}

	/**
	 * Remove IG code from the url
	 * @param array $args
	 * @since 1.0.0
	 */
	public function user_after_updating_profile( $user_id, $args, $to_update ) {
		$flush_option = false;
		$fields = UM()->builtin()->all_user_fields;
		$submitted_fields = array_keys( $args['submitted'] );

		foreach ( $submitted_fields as $key ) {
			if ( isset( $fields[$key]['type'] ) && 'instagram_photo' == $fields[$key]['type'] ) {
				if ( empty( $args['submitted'][$key] ) ) {
					$flush_option = true;
				}
			}
		}

		if ( $flush_option ) {
			delete_user_meta( $user_id, 'um_instagram_code' );
			delete_user_meta( $user_id, 'um_instagram_token' );
		}
	}

	/**
	 * Checks Instagram extension enable
	 * @return boolean
	 * @since  1.0.1
	 */
	public function is_enabled() {
		$enable_instagram_photo = UM()->options()->get( 'enable_instagram_photo' );

		if ( $enable_instagram_photo ) {
			return true;
		}

		return false;
	}

	/**
	 * Hide instagram field
	 * @param  string $output
	 * @param  string $form_mode
	 * @return boolean
	 * @since  1.0.1
	 */
	public function instagram_photo_form_show_field( $output, $form_mode ) {
		return '';
	}

}
