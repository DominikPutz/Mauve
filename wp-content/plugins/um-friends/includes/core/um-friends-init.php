<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Friends_API
 */
class UM_Friends_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Friends_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Friends_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_friends'] = $this;
		add_filter( 'um_call_object_Friends_API', array( &$this, 'get_this' ) );

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_friends_path . 'includes/core/um-friends-widget.php';
		require_once um_friends_path . 'includes/core/um-friends-online-widget.php';
		add_action( 'widgets_init', array(&$this, 'widgets_init' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		add_filter( 'um_email_templates_path_by_slug', array( &$this, 'email_templates_path_by_slug' ), 10, 1 );

		add_action( 'wp_ajax_um_friends_approve', array( $this->api(), 'ajax_friends_approve' ) );
		add_action( 'wp_ajax_um_friends_add', array( $this->api(), 'ajax_friends_add' ) );
		add_action( 'wp_ajax_um_friends_unfriend', array( $this->api(), 'ajax_friends_unfriend' ) );
		add_action( 'wp_ajax_um_friends_cancel_request', array( $this->api(), 'ajax_friends_cancel_request' ) );
	}


	/**
	 * @param $slugs
	 *
	 * @return mixed
	 */
	function email_templates_path_by_slug( $slugs ) {
		$slugs['new_friend'] = um_friends_path . 'templates/email/';
		$slugs['new_friend_request'] = um_friends_path . 'templates/email/';
		return $slugs;
	}


	/**
	 * @param $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_friends\core\Friends_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_friends_setup'] ) ) {
			UM()->classes['um_friends_setup'] = new um_ext\um_friends\core\Friends_Setup();
		}
		return UM()->classes['um_friends_setup'];
	}


	/**
	 * @return um_ext\um_friends\core\Friends_Main_API()
	 */
	function api() {
		if ( empty( UM()->classes['um_friends_api'] ) ) {
			UM()->classes['um_friends_api'] = new um_ext\um_friends\core\Friends_Main_API();
		}
		return UM()->classes['um_friends_api'];
	}


	/**
	 * @return um_ext\um_friends\core\Friends_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_friends_enqueue'] ) ) {
			UM()->classes['um_friends_enqueue'] = new um_ext\um_friends\core\Friends_Enqueue();
		}
		return UM()->classes['um_friends_enqueue'];
	}


	/**
	 * @return um_ext\um_friends\core\Friends_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_friends_shortcode'] ) ) {
			UM()->classes['um_friends_shortcode'] = new um_ext\um_friends\core\Friends_Shortcode();
		}
		return UM()->classes['um_friends_shortcode'];
	}


	/**
	 * @return um_ext\um_friends\core\Friends_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_friends_member_directory'] ) ) {
			UM()->classes['um_friends_member_directory'] = new um_ext\um_friends\core\Friends_Member_Directory();
		}
		return UM()->classes['um_friends_member_directory'];
	}


	/**
	 * Init
	 */
	function init() {
		$this->enqueue();
		$this->shortcode();
		$this->member_directory();

		// Actions
		require_once um_friends_path . 'includes/core/actions/um-friends-profile.php';
		require_once um_friends_path . 'includes/core/actions/um-friends-notifications.php';
		require_once um_friends_path . 'includes/core/actions/um-friends-admin.php';
		require_once um_friends_path . 'includes/core/actions/um-friends-account.php';

		// Filters
		require_once um_friends_path . 'includes/core/filters/um-friends-settings.php';
		require_once um_friends_path . 'includes/core/filters/um-friends-profile.php';
		require_once um_friends_path . 'includes/core/filters/um-friends-admin.php';
		require_once um_friends_path . 'includes/core/filters/um-friends-account.php';

	}


	/**
	 *
	 */
	function widgets_init() {
		register_widget( 'um_my_friends' );
		register_widget( 'um_my_friends_online' );
	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_friends', -10, 1 );
function um_init_friends() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Friends_API', true );
	}
}