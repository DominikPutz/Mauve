<?php
namespace um_ext\um_mycred\core\hooks;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Init
 *
 * @package um_ext\um_mycred\core\hooks
 */
class Init {


	/**
	 * Init constructor.
	 */
	function __construct() {
		add_filter( 'mycred_setup_hooks', array( $this, 'register_custom_hooks' ), 10, 2 );
		add_filter( 'mycred_all_references', array( $this, 'references' ), 10, 1 );
	}


	/**
	 * Core Hooks
	 *
	 * @param array $installed
	 * @param $point_type
	 *
	 * @return array
	 */
	public function register_custom_hooks( $installed, $point_type ) {
		// Register
		$installed['um-user-register'] = array(
			'title'        => __( 'Ultimate Member - Registration', 'um-mycred' ),
			'description'  => __( 'Award points for register hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Register_Hooks' )
		);

		// Login
		$installed['um-user-login'] = array(
			'title'        => __( 'Ultimate Member - Login', 'um-mycred' ),
			'description'  => __( 'Award points for login hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Login_Hooks' )
		);

		// Profile
		$installed['um-user-profile'] = array(
			'title'        => __( 'Ultimate Member - Profile', 'um-mycred' ),
			'description'  => __( 'Award points for profile hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Profile_Hooks' )
		);

		// Account
		$installed['um-user-account'] = array(
			'title'        => __( 'Ultimate Member - Account', 'um-mycred' ),
			'description'  => __( 'Award points for account hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Account_Hooks' )
		);

		// Member Directory
		$installed['um-member-directory'] = array(
			'title'        => __( 'Ultimate Member - Member Directory', 'um-mycred' ),
			'description'  => __( 'Award points for Member Directory hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Member_Directory_Hooks' )
		);


		$installed = apply_filters( 'um_mycred_hooks_installed__filter', $installed );

		return $installed;
	}


	/**
	 * @param $hooks
	 *
	 * @return mixed
	 */
	public function references( $hooks ) {

		$hooks = array_merge( $hooks, array(
			'um-user-register'  => __( 'Ultimate Member - Completing registration', 'um-mycred' ),
			'um-user-login'     => __( 'Ultimate Member - Logging via UM Login Form', 'um-mycred' ),
			'profile_photo'     => __( 'Ultimate Member - Uploading Profile Photo', 'um-mycred' ),
			'cover_photo'       => __( 'Ultimate Member - Uploading Cover Photo', 'um-mycred' ),
			'update_profile'    => __( 'Ultimate Member - Updating Profile', 'um-mycred' ),
			'update_account'    => __( 'Ultimate Member - Updating Account', 'um-mycred' ),
			'member_search'     => __( 'Ultimate Member - Using Search in Members Directory', 'um-mycred' ),
		) );

		return $hooks;
	}

}