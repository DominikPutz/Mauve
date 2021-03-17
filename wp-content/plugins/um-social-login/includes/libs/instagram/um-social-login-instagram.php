<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Social_Login_Instagram
 */
class UM_Social_Login_Instagram {


	/**
	 * @var string
	 */
	private $client_id = '';


	/**
	 * @var string
	 */
	private $client_secret = '';


	/**
	 * @var string
	 */
	private $callback_url = '';


	/**
	 * @var string
	 */
	private $login_url = '';


	/**
	 * UM_Social_Login_Instagram constructor.
	 */
	function __construct() {
		
		add_action( 'init', array( &$this, 'init' ), 0 );
		
		add_action( 'init', array( &$this, 'get_auth' ) );

		add_action( 'template_redirect', array( &$this, 'redirect_authentication' ), 1 );

	}


	/**
	 * Init Provider variables
	 */
	function init() {

		UM()->Social_Login_API()->session_start();

		$this->client_id = trim( UM()->options()->get( 'instagram_client_id' ) );
		$this->client_secret = trim( UM()->options()->get( 'instagram_client_secret' ) );

		$this->callback_url = UM()->Social_Login_API()->get_redirect_url();
		$this->callback_url = add_query_arg( 'provider', 'instagram', $this->callback_url );
		$this->callback_url = remove_query_arg( 'code', $this->callback_url );

	}


	/**
	 * Get Provider Auth
	 *
	 * @throws Exception
	 */
	function get_auth() {
		if ( isset( $_REQUEST['code'] ) && isset( $_SESSION['um_social_login'] ) && 'instagram' == $_SESSION['um_social_login'] ) {

			$instagram = $this->get_provider();

			$token = false;
			
			if( isset( $_SESSION['insta_access_token'] ) && isset( $_SESSION['insta_user_data'] ) ){
				$token = $_SESSION['insta_access_token'];
				$user_data = $_SESSION['insta_user_data'];
			}else{

				$code = $_REQUEST['code'];
				$data = $instagram->getOAuthToken( $code );

				try{
					$instagram->setAccessToken( $data->access_token );
					$user_data = (array) $instagram->getUser( $data->user_id, true );
				}catch( Exception $e ){
					wp_die( 'UM Social Login - Instagram: ' . $e->getMessage(), 'UM Social Login - Instagram Error', array( 'back_link' => true ) );
				}

				if ( isset( $data->code ) && $data->code == 400 ) {
					wp_die( 'UM Social Login - Instagram: ' . $data->error_type . ' - ' . $data->error_message, 'UM Social Login - Instagram Error', array( 'back_link' => true ) );
				} else {
					if ( isset( $data->access_token ) ) {
						$token = $data->access_token;
						$_SESSION['insta_access_token'] = $token;
						$_SESSION['insta_user_data'] = $user_data;
					}
					$user_data = $_SESSION['insta_user_data'];
				}
			}

			$profile = (array) $user_data;

			$username = isset( $profile['username'] )? $profile['username'] : '';
			$profile_id = isset( $profile['id'] ) ? $profile['id'] : '';
			$profile_picture = isset( $profile['profile_picture'] ) ? $profile['profile_picture'] : '';

			// prepare the array that will be sent
			$profile['username'] = $username;
			$profile['user_login'] = $username;
			$profile['email_exists'] = '';
			$profile['username_exists'] = '';

			// provider identifier
			$profile['_uid_instagram'] = $profile_id;

			$profile['_save_synced_profile_photo'] = $profile_picture;

			$profile['_save_instagram_handle'] = '@' . $username;
			$profile['_save_instagram_link'] = 'https://instagram.com/' . $username;
			$profile['_save_instagram_photo_url_dyn'] = $profile_picture;

			// have everything we need?
			UM()->Social_Login_API()->resume_registration( $profile, 'instagram' );

		}

	}


	/**
	 * @throws Exception
	 */
	function redirect_authentication() {

		if ( isset( $_REQUEST['um_social_login'] ) && $_REQUEST['um_social_login'] == "instagram" ) {

			$_SESSION['um_social_login'] = 'instagram';

			unset( $_SESSION['insta_access_token'] );
			unset( $_SESSION['insta_user_data'] );

			wp_redirect( $this->login_url() );
			exit();
		}

	}


	/**
	 * @return Instagram
	 *
	 * @throws Exception
	 */
	function get_provider() {
		if ( ! class_exists( 'Instagram' ) ) {
			require_once um_social_login_path . 'includes/libs/instagram/api/Instagram.php';
		}

		try{

			if( isset( $_REQUEST['code'] ) ){
				$this->callback_url = remove_query_arg("provider", $this->callback_url );
			}

			$instagram = new Instagram( array(
				'apiKey'      => $this->client_id,
				'apiSecret'   => $this->client_secret,
				'apiCallback' => $this->callback_url
			) );
		}catch( Exception $e ){

			wp_die( "SSO Error - Instagram: ". $e->getMessage() );
		}

		return $instagram;
	}


	/**
	 * @return string
	 *
	 *
	 * @throws Exception
	 */
	function login_url() {

		if ( ! isset( $_REQUEST['um_social_login'] ) ) {
			$this->login_url = add_query_arg( array( 'um_social_login' => 'instagram', 'um_social_login_ref' => UM()->Social_Login_API()->shortcode_id ), um_get_core_page( 'login' ) );

			if ( isset( $_SESSION['um_social_login_redirect'] ) ) {
				if ( ! isset( $_REQUEST['code'] ) && ! isset( $_REQUEST['state'] ) ) {
					$this->login_url = add_query_arg( 'redirect_to', $_SESSION['um_social_login_redirect'], $this->login_url );
					$_SESSION['um_social_login_redirect_after'] = $_SESSION['um_social_login_redirect'];
				}
			}
		} else {
			$instagram = $this->get_provider();
			$this->login_url = $instagram->getLoginUrl();

		}

		return $this->login_url;
	}

}