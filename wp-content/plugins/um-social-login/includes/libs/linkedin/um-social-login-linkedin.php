<?php

class UM_Social_Login_LinkedIn {

	public $login_url_called = 0;

	public $api_version = 2;

	function __construct() {
		
		add_action('init', array(&$this, 'init'));
		
		add_action('init', array(&$this, 'get_auth'));

		add_action('template_redirect', array( &$this,'redirect_authentication'), 1 );

		$this->api_version = apply_filters('um_social_login_linked_api_version', 2 );


	}

	function redirect_authentication(){
		
		if( isset( $_REQUEST['um_social_login'] ) &&  $_REQUEST['um_social_login'] == "linkedin"  ){

			return wp_redirect( $this->login_url() );
		}

	}

	/***
	***	@load
	***/
	function load() {
		if( ! class_exists( 'LinkedIn' ) ){
			require_once um_social_login_path . 'includes/libs/linkedin/api/LinkedIn.php';
		}
	}

	/***
	***	@init
	***/
	function init() {
		$this->api_key = trim( UM()->options()->get('linkedin_api_key') );
		$this->api_secret = trim( UM()->options()->get('linkedin_api_secret') );
		if( method_exists ( 'UM_Social_Login_API','get_redirect_url' ) ){
			$this->oauth_callback = UM()->Social_Login_API()->get_redirect_url();
		}
		$this->oauth_callback = add_query_arg( 'provider', 'linkedin', $this->oauth_callback );

		$this->login_url = '';

	}


	/***
	***	@Get auth
	***/
	function get_auth() {

		if ( isset($_REQUEST['provider']) && $_REQUEST['provider'] == 'linkedin'  ) {
				
			$this->load();

			if( isset( $_REQUEST['error'] )  && $_REQUEST['error']  == 'unauthorized_scope_error' ){
					
				wp_redirect(  add_query_arg( 'err', esc_attr( 'um_social_unauthorized_scope_error' ), um_get_core_page( 'login' ) ) ); 
				exit;

			}


			if( isset( $_REQUEST['error'] )  && $_REQUEST['error']  == 'user_cancelled_login' ){
					
				wp_redirect(  add_query_arg( 'err', esc_attr( 'um_social_user_denied' ), um_get_core_page( 'login' ) ) ); 
				exit;

			}
				
			$provider = new LinkedIn(
				array(
					'api_key' => $this->api_key, 
					'api_secret' => $this->api_secret, 
					'callback_url' => $this->oauth_callback,
					'version'	=> $this->api_version,
				)
			);

			$code = $_REQUEST['code'];	

			// invalid token: abort
			if( ! isset( $_POST['_um_social_login'] )  && isset( $_POST ) && empty( $_SESSION['linkedin_access_token'] )  ){
				
					try{					
						
	            		$accessToken = $provider->getAccessToken( $code  );
	            		  

					}catch( Exception $e ){
					 	wp_die(  "UM Social Login - LinkedIn SDK Error Message:"
					 		."<br/>".$e->getMessage()
					 		."<br/>Error Code: ".$i
					 		."<br/>Callback URL: ".$this->oauth_callback 
					 		."<br/>Session Code: <pre>".$code."</pre>"
					 	);
					}

			}


			if ( empty( $accessToken ) ) {
				$accessToken = ! empty( $_SESSION['linkedin_access_token'] ) ? $_SESSION['linkedin_access_token'] : '';
			}

			$profile = array();

			if ( ! empty( $accessToken ) ) {

					$_SESSION['linkedin_access_token'] = (string) $accessToken;

					$provider->setAccessToken( $accessToken );
				
					try{

						if( $this->api_version == 1 ){

							$request_data = array('id','first-name','last-name','email-address', 'public-profile-url', 'picture-url' );

							$request_data = apply_filters('um_social_login_linked_request_data', $request_data );
							
							$info = $provider->get( 'v1/people/~', $request_data );

						}elseif( $this->api_version == 2 ){

							$request_data = array(
								'id',
								'firstName',
								'lastName',
								'profilePicture(displayImage~:playableStreams(elements(*)))',
							);

							$request_data = apply_filters('um_social_login_linked_request_data', $request_data );

							$info = $provider->get('v2/me?projection=('.implode(",", $request_data ).')');
							$email = $provider->get('v2/emailAddress?q=members&projection=(elements*(handle~))');

							if( isset( $email['elements'][0]['handle~']['emailAddress'] ) ){
								$info['emailAddress'] = $email['elements'][0]['handle~']['emailAddress'];
							}

							if( isset( $info['firstName']['preferredLocale']['country'] ) && isset( $info['firstName']['preferredLocale']['language'] ) ){
								$info['preferredLocaleFirstName'] = $info['firstName']['preferredLocale']['language'].'_'.$info['firstName']['preferredLocale']['country'];

								if( isset( $info['firstName']['localized'][ $info['preferredLocaleFirstName'] ] ) ){
									$info['firstName'] = $info['firstName']['localized'][ $info['preferredLocaleFirstName'] ];
								}
							}

							if( isset( $info['lastName']['preferredLocale']['country'] ) && isset( $info['lastName']['preferredLocale']['language'] ) ){
								$info['preferredLocalelastName'] = $info['lastName']['preferredLocale']['language'].'_'.$info['lastName']['preferredLocale']['country'];

								if( isset( $info['lastName']['localized'][ $info['preferredLocalelastName'] ] ) ){
									$info['lastName'] = $info['lastName']['localized'][ $info['preferredLocalelastName'] ];
								}
							}

							if( isset( $info['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'] ) ){
								$identifiers = $info['profilePicture']['displayImage~']['elements'][0]['identifiers'];
								foreach( $identifiers as $k => $ident ){
									$info['pictureUrls']['values'][ ] = $ident['identifier'];
								}
							}

						}

					}catch( Exception $e ){
						wp_die(  "UM Social Login - LinkedIn SDK Error Message: <br/>".$e->getMessage() );
					}

     				if ( isset ( $info['pictureUrls'] ) 
						&& isset( $info['pictureUrls']['values'] ) 
						&& isset( $info['pictureUrls']['values'][0] ) ) {
							$profile['picture-original'] = $info['pictureUrls']['values'][0];
					} else if ( isset( $info['pictureUrl'] ) ) {
							$profile['picture-original'] = $info['pictureUrl'];
					}else{
						$profile['picture-url'] = um_get_default_avatar_uri();
						$profile['picture-original'] = um_get_default_avatar_uri();
					}

					if ( isset( $profile['picture-original'] ) ) {
							$profile['_save_synced_profile_photo'] = $profile['picture-original'];
					}

					if ( isset( $profile['picture-url'] ) ) {
							$profile['_save_linkedin_photo_url_dyn'] = $profile['picture-url'];
					}
					
					
					// prepare the array that will be sent
					$profile['user_email'] = $info['emailAddress'];
					$profile['first_name'] = $info['firstName'];
					$profile['last_name']  = $info['lastName'];
					
					// username/email exists
					$profile['email_exists'] = $info['emailAddress'];
					$profile['username_exists'] = $info['emailAddress'];
					
					// provider identifier
					$profile['_uid_linkedin'] = $info['id'];
					
					$profile['_save_linkedin_handle'] = $info['firstName'] . ' ' . $info['lastName'];
					$profile['_save_linkedin_link'] = $info['publicProfileUrl'];

					if ( isset( $profile['picture-original'] ) ) {
						$profile['_save_synced_profile_photo'] = $profile['picture-original'];
					}
				
					if ( isset( $profile['picture-url'] ) ) {
						$profile['_save_linkedin_photo_url_dyn'] = $profile['picture-url'];
					}
					
					$profile = apply_filters('um_social_login_linked_profile', $profile, $info );
			}


			// have everything we need?
	        UM()->Social_Login_API()->resume_registration( $profile, 'linkedin' );

	       if( ! empty( $accessToken ) ){
				//unset( $_SESSION['linkedin_access_token'] );
			}
	      
					
		
			
		}

	}
		
	/***
	***	@get login uri
	***/
	function login_url() {

		if( ! isset( $_REQUEST['um_social_login'] ) ){
			$this->login_url = um_get_core_page('login');
			$this->login_url = add_query_arg('um_social_login','linkedin', $this->login_url );
			$this->login_url = add_query_arg('um_social_login_ref', UM()->Social_Login_API()->shortcode_id, $this->login_url );
			if( isset( $_SESSION['um_social_login_redirect'] ) ){
				if ( ! isset( $_REQUEST['code'] ) && ! isset( $_REQUEST['state'] )  ) {
				$this->login_url = add_query_arg('redirect_to', $_SESSION['um_social_login_redirect'], $this->login_url );
					$_SESSION['um_social_login_redirect_after'] = $_SESSION['um_social_login_redirect'];
				}
			}
		}else{

			if( ! isset( $_REQUEST['provider'] ) &&  empty( $this->login_url ) ){
					
					$this->load();
					$provider = new LinkedIn(
						 array(
							'api_key' => $this->api_key, 
							'api_secret' => $this->api_secret, 
							'callback_url' => $this->oauth_callback,
							'version'	=> $this->api_version,
						)
					);

					if( $this->api_version == 1 ){
						$scope = array(
						    LinkedIn::SCOPE_BASIC_PROFILE, 
						    LinkedIn::SCOPE_EMAIL_ADDRESS, 
						);
					}elseif( $this->api_version == 2 ){
						$scope = array(
							LinkedIn::SCOPE_LITE_PROFILE, 
							LinkedIn::SCOPE_EMAIL_ADDRESS, 
						);
					}

					$scope = apply_filters('um_social_login_linked_scope', $scope );
					$url = $provider->getLoginUrl( $scope );

					$this->login_url = $url;
	
			}
		}

		unset( $_SESSION['um_social_login_linked_code'] );
	
		
		
		return $this->login_url;
		
	}
		
}