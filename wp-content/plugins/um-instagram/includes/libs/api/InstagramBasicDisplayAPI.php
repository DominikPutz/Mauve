<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Instagram Basic Display API class
 *
 * API Documentation: https://developers.facebook.com/docs/instagram-basic-display-api/
 *
 * @author UM
 * @since 2019-10-16
 * @version 1.1
 */
class InstagramBasicDisplayAPI extends Instagram {

	/**
	 * The API base URL
	 */
	const API_URL = 'https://graph.instagram.com/';

	/**
	 * The API OAuth URL
	 */
	const API_OAUTH_URL = 'https://api.instagram.com/oauth/authorize';

	/**
	 * The OAuth token URL
	 */
	const API_OAUTH_TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

	/**
	 * The Instagram API Key
	 *
	 * @var string
	 */
	private $_apikey;

	/**
	 * The Instagram OAuth API secret
	 *
	 * @var string
	 */
	private $_apisecret;

	/**
	 * The callback URL
	 *
	 * @var string
	 */
	private $_callbackurl;

	/**
	 * The user access token
	 *
	 * @var string
	 */
	private $_accesstoken;

	/**
	 * Whether a signed header should be used
	 *
	 * @var boolean
	 */
	private $_signedheader = false;

	/**
	 * Available scopes
	 *
	 * @var array
	 */
	private $_scopes = array( 'user_profile', 'user_media' );

	/**
	 * A comma-separated list of User fields
	 * @see https://developers.facebook.com/docs/instagram-basic-display-api/reference/user#fields
	 *
	 * @var array
	 */
	private $_fields = array( 'id', 'media_count', 'username' );

	/**
	 * A comma-separated list of Media fields
	 * @see https://developers.facebook.com/docs/instagram-basic-display-api/reference/media#fields
	 *
	 * @var array
	 */
	private $_fields_media = array( 'id', 'caption', 'media_type', 'media_url', 'permanlink' );

	/**
	 * Default constructor
	 *
	 * @param array|string $config          Instagram configuration data
	 * @return void
	 */
	public function __construct( $config ) {
		parent::__construct( $config );
	}

	/**
	 * The call operator
	 *
	 * @param string $function              API resource path
	 * @param array [optional] $params      Additional request parameters
	 * @param boolean [optional] $auth      Whether the function requires an access token
	 * @param string [optional] $method     Request type GET|POST
	 * @return mixed
	 */
	public function _makeCall( $function, $auth = false, $params = null, $method = 'GET' ) {

		if ( isset( $this->_accesstoken ) ) {
			$authMethod = '?access_token=' . $this->getAccessToken();
		} else {
			throw new \Exception( "Error: _makeCall() | $function - This method requires an authenticated users access token." );
		}

		if ( isset( $params ) && is_array( $params ) ) {
			$paramString = '&' . http_build_query( $params );
		} else {
			$paramString = null;
		}

		$apiCall = self::API_URL . $function . $authMethod;
		if ( 'GET' === $method ) {
			$apiCall .= $paramString;
		}

		// signed header of POST/DELETE requests
		$headerData = array( 'Accept: application/json' );
		if ( $this->_signedheader && 'GET' !== $method ) {
			$headerData[] = 'X-Insta-Forwarded-For: ' . $this->_signHeader();
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $apiCall );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headerData );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 20 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 90 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

		if ( 'POST' === $method ) {
			curl_setopt( $ch, CURLOPT_POST, count( $params ) );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, ltrim( $paramString, '&' ) );
		} else if ( 'DELETE' === $method ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		}

		$jsonData = curl_exec( $ch );
		if ( !$jsonData ) {
			throw new \Exception( "Error: _makeCall() - cURL error: " . curl_error( $ch ) );
		}
		curl_close( $ch );

		$data = json_decode( $jsonData );

		/* DEBUG */
		if ( isset( $data->error ) && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
			error_log( 'UM Instagram Error: _makeCall()' );
			error_log( 'UM Instagram: REQUEST => ' . $apiCall );
			error_log( 'UM Instagram: RESPONSE => ' . $jsonData );
		}

		return $data;
	}

	/**
	 * The OAuth call operator
	 *
	 * @param array $apiData                The post API data
	 * @return mixed
	 */
	private function _makeOAuthCall( $apiData ) {
		$apiHost = self::API_OAUTH_TOKEN_URL;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $apiHost );
		curl_setopt( $ch, CURLOPT_POST, count( $apiData ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $apiData ) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Accept: application/json' ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 90 );

		$jsonData = curl_exec( $ch );
		if ( !$jsonData ) {
			throw new \Exception( "Error: _makeOAuthCall() - cURL error: " . curl_error( $ch ) );
		}
		curl_close( $ch );

		$data = json_decode( $jsonData );

		/* DEBUG */
		if ( isset( $data->error ) && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
			error_log( 'UM Instagram Error: _makeOAuthCall()' );
			error_log( 'UM Instagram: REQUEST => ' . http_build_query( $apiData ) );
			error_log( 'UM Instagram: RESPONSE => ' . $jsonData );
		}

		return $data;
	}

	/**
	 * Access Token Setter
	 *
	 * @param object|string $data
	 * @return void
	 */
	public function setAccessToken( $data ) {
		$token = is_object( $data ) ? $data->access_token : $data;
		$this->_accesstoken = $token;
	}

	/**
	 * Access Token Getter
	 *
	 * @return string
	 */
	public function getAccessToken() {
		return $this->_accesstoken;
	}

	/**
	 * Fields Setter
	 *
	 * @param string $fields
	 * @return void
	 */
	public function setFields( $fields ) {
		if ( is_array( $fields ) ) {
			$this->_fields = $fields;
		}
	}

	/**
	 * Media fields Getter
	 *
	 * @return string
	 */
	public function getFields( $fields = array() ) {
		if ( empty( $fields ) ) {
			return $this->_fields;
		}
		return $fields;
	}

	/**
	 * Fields Setter
	 *
	 * @param string $fields
	 * @return void
	 */
	public function setFieldsMedia( $fields ) {
		if ( is_array( $fields ) ) {
			$this->_fields_media = $fields;
		}
	}

	/**
	 * Media fields Getter
	 *
	 * @return string
	 */
	public function getFieldsMedia( $fields = array() ) {
		if ( empty( $fields ) ) {
			return $this->_fields_media;
		}
		return $fields;
	}

	/**
	 * Generates the OAuth login URL
	 *
	 * @see https://developers.facebook.com/docs/instagram-basic-display-api/guides/getting-access-tokens-and-permissions
	 *
	 * @param array [optional] $scope       Requesting additional permissions
	 * @return string                       Instagram OAuth login URL
	 */
	public function getLoginUrl( $scope = array( 'user_profile', 'user_media' ) ) {
		if ( is_array( $scope ) && !array_diff( $this->_scopes, $scope ) ) {
			return self::API_OAUTH_URL
				. '?app_id=' . $this->getApiKey()
				. '&redirect_uri=' . urlencode( $this->getApiCallback() )
				. '&scope=' . implode( ',', $scope )
				. '&response_type=code';
		} elseif ( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
			error_log( 'UM Instagram Error: $scope=' . json_encode( $scope ) );
			throw new \Exception( "Error: getLoginUrl() - The parameter isn't an array or invalid scope permissions used." );
		}
	}

	/**
	 * Get the OAuth data of a user by the returned callback code
	 *
	 * @see https://developers.facebook.com/docs/instagram-basic-display-api/guides/getting-access-tokens-and-permissions
	 *
	 * @param string $code                  OAuth2 code variable (after a successful login)
	 * @param boolean [optional] $token     If it's true, only the access token will be returned
	 * @return mixed
	 */
	public function getOAuthToken( $code, $token = false ) {
		$apiData = array(
			'app_id'			 => $this->getApiKey(),
			'app_secret'	 => $this->getApiSecret(),
			'grant_type'	 => 'authorization_code',
			'redirect_uri' => $this->getApiCallback(),
			'code'				 => $code
		);

		$result = $this->_makeOAuthCall( $apiData );

		/* DEBUG */
		if ( isset( $result->error_message ) && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
			error_log( 'UM Instagram Error: getOAuthToken() - ' . $result->error_message );
			error_log( 'UM Instagram Error: $apiData=' . json_encode( $apiData ) );
			error_log( 'UM Instagram Error: $result=' . json_encode( $result ) );
		}

		return $token ? $result->access_token : $result;
	}

	/**
	 * Get user info
	 *
	 * @param integer [optional] $id        Instagram user ID
	 * @param array [optional] $params
	 * @return mixed
	 */
	public function getUser( $id = 0, $params = array() ) {
		if ( empty( $id ) && isset( $this->_accesstoken ) ) {
			$id = 'me';
		}
		if ( empty( $params['fields'] ) ) {
			$params['fields'] = implode( ',', $this->getFields() );
		}
		return $this->_makeCall( $id, false, $params );
	}

	/**
	 * Get user media info
	 *
	 * @param integer [optional] $id        Instagram user ID
	 * @param array [optional] $params
	 * @return mixed
	 */
	public function getUserMedia( $id = 0, $params = array() ) {
		if ( empty( $id ) && isset( $this->_accesstoken ) ) {
			$id = 'me';
		}
		if ( empty( $params['fields'] ) ) {
			$params['fields'] = implode( ',', $this->getFieldsMedia() );
		}
		return $this->_makeCall( "$id/media", false, $params );
	}

}
