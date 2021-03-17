<?php namespace um_ext\um_instagram\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * class Instagram_Connect
 *
 * @since  1.0.0
 */
class Instagram_Connect {

	/**
	 * Instagram API type
	 * @var int
	 */
	public $api_version;

	/**
	 * Instagram App ID
	 * @var int
	 */
	public $app_id;


	/**
	 * Instagram App Secret
	 * @var string
	 */
	public $app_secret;

	/**
	 * @var int
	 */
	public $client_id;


	/**
	 * @var string
	 */
	public $client_secret;


	/**
	 * @var string
	 */
	public $callback_url;


	/**
	 * init
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( &$this, 'load' ), 99 );
		add_action( 'plugins_loaded', array( &$this, 'get_auth' ), 100 );
	}


	/**
	 * Instagram API object
	 * @since  2.0.5
	 *
	 * @staticvar \Instagram $instagram
	 * @param $api_data
	 *
	 * @return \Instagram
	 * @throws \Exception
	 */
	public function call_API( $api_data = array() ) {
		static $instagram = null;

		if ( !class_exists( '\Instagram' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/api/Instagram.php';
		}
		if ( !class_exists( '\InstagramBasicDisplayAPI' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/api/InstagramBasicDisplayAPI.php';
		}

		switch ( $this->api_version ) {
			case 'facebook':
				$api_data = array_merge( array(
						'apiKey'			 => $this->app_id,
						'apiSecret'		 => $this->app_secret,
						'apiCallback'	 => $this->callback_url
						), $api_data );
				$instagram = new \InstagramBasicDisplayAPI( $api_data );
				break;

			case 'instagram':
			default:
				$api_data = array_merge( array(
						'apiKey'			 => $this->client_id,
						'apiSecret'		 => $this->client_secret,
						'apiCallback'	 => add_query_arg( 'um-connect-instagram', 'true', $this->callback_url )
						), $api_data );
				$instagram = new \Instagram( $api_data );
				break;
		}

		return $instagram;
	}


	/**
	 * Get Authorization URL
	 * @since  1.0.0
	 *
	 * @return string Login url for App authorization
	 * @throws \Exception
	 */
	public function connect_url() {
		$user_id = um_profile_id();
		$profile_url = add_query_arg( array(
				'profiletab' => 'main',
				'um_action' => 'edit'
		), um_user_profile_url( $user_id ) );
		update_user_meta( $user_id, 'um_instagram_profile_url', $profile_url );
		
		return $this->call_API()->getLoginUrl();
	}


	/**
	 * Get authorization callback response
	 * action hook: template_redirect
	 *
	 * @since  1.0.0
	 * @throws \Exception
	 */
	public function get_auth() {

		$code = function_exists( 'filter_input' ) ? filter_input( INPUT_GET, 'code' ) : $_REQUEST['code'];

		// Only logged in members can edit their profile
		if ( !$code || !is_user_logged_in() ) {
			return;
		}
		
		$user_id = um_profile_id();

		// Verify that it is response from instagram.com
		if ( empty( $_REQUEST['um-connect-instagram'] ) && (empty( $_SERVER["HTTP_REFERER"] ) || !substr_count( $_SERVER["HTTP_REFERER"], 'instagram.com' )) ) {
			return;
		}

		// Exchange the Code For a Token			
		$result = $this->call_API()->getOAuthToken( $code );

		// Save error message
		if ( is_object( $result ) && isset( $result->error_message ) ) {
			update_user_meta( $user_id, 'um_instagram_error_message', __( 'UM Instagram Error: Wrong Access Token.', 'um-instagram' ) . "<br><strong>$result->error_message</strong>" );
		}

		// Save Token and go back to the Profile page
		if ( is_object( $result ) && isset( $result->access_token ) ) {
			update_user_meta( $user_id, 'um_instagram_token', $result->access_token );
			update_user_meta( $user_id, 'um_instagram_token_new', $result->access_token );
		}

		// DEBUG
		if ( !is_object( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			_e( 'UM Instagram Error: Wrong Access Token.', 'um-instagram' );
			echo '<pre>';
			print_r( $result );
			echo '</pre>';
		}

		$profile_url = get_user_meta( $user_id, 'um_instagram_profile_url', true );
		echo "<script type=\"text/javascript\">window.close();window.opener.location.href='" . esc_url_raw( $profile_url ) . "';</script>";

		exit;
	}


	/**
	 * Get current user's access token
	 *
	 * @param  string $metakey field meta key
	 * @param  int $user_id User ID
	 * @return string | boolean  returns token strings on success, otherwise return false when empty token
	 *
	 * @since  1.0.0
	 */
	public function get_user_token( $metakey = '', $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = um_user( 'ID' );
			if ( empty( $user_id ) ) {
				return false;
			}
		}

		$token = get_user_meta( $user_id, $metakey, true );

		$um_instagram_code = apply_filters( 'um_instagram_code_in_user_meta', true );
		if ( ! $token && $um_instagram_code ) {
			$token = get_user_meta( $user_id, 'um_instagram_token', true );
		}
		if ( ! $token && $um_instagram_code ) {
			$token = get_user_meta( $user_id, 'um_instagram_code', true );
		}

		return $token;
	}


	/**
	 * Checks if session has been started
	 *
	 * @return bool
	 */
	public function is_session_started() {
		if ( php_sapi_name() !== 'cli' ) {
			return session_status() === PHP_SESSION_ACTIVE ? true : false;
		}

		return false;
	}


	/**
	 * Prepare variables
	 * action hook: template_redirect
	 *
	 * @since  1.0.0
	 */
	public function load() {
		$this->api_version = trim( UM()->options()->get( 'instagram_photo_api_version' ) );
		if( empty( $this->api_version ) ){
			$this->api_version = 'instagram';
		}

		$this->app_id = trim( UM()->options()->get( 'instagram_photo_app_id' ) );
		$this->app_secret = trim( UM()->options()->get( 'instagram_photo_app_secret' ) );
		$this->client_id = trim( UM()->options()->get( 'instagram_photo_client_id' ) );
		$this->client_secret = trim( UM()->options()->get( 'instagram_photo_client_secret' ) );

		$this->callback_url = apply_filters( 'um_instagram_callback_url', site_url( '/' ) );
	}
}