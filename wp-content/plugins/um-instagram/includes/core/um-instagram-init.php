<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Instagram_API
 * @since 1.0.0
 */
class UM_Instagram_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Instagram_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_instagram'] = $this;
		add_filter( 'um_call_object_Instagram_API', array( &$this, 'get_this' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		$this->includes();
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
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
	 * @return um_ext\um_instagram\core\Instagram_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_instagram_setup'] ) ) {
			UM()->classes['um_instagram_setup'] = new um_ext\um_instagram\core\Instagram_Setup();
		}
		return UM()->classes['um_instagram_setup'];
	}


	/**
	 * @return um_ext\um_instagram\core\Instagram_Connect()
	 */
	function connect() {
		if ( empty( UM()->classes['um_instagram_connect'] ) ) {
			UM()->classes['um_instagram_connect'] = new um_ext\um_instagram\core\Instagram_Connect();
		}
		return UM()->classes['um_instagram_connect'];
	}


	/**
	 * @return um_ext\um_instagram\admin\Instagram_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_instagram_admin'] ) ) {
			UM()->classes['um_instagram_admin'] = new um_ext\um_instagram\admin\Instagram_Admin();
		}
		return UM()->classes['um_instagram_admin'];
	}


	/**
	 * @return um_ext\um_instagram\core\Instagram_Public()
	 */
	function frontend() {
		if ( empty( UM()->classes['um_instagram_public'] ) ) {
			UM()->classes['um_instagram_public'] = new um_ext\um_instagram\core\Instagram_Public();
		}
		return UM()->classes['um_instagram_public'];
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function includes() {
		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->connect();
		$this->frontend();
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_instagram', -10, 1 );
function um_init_instagram() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Instagram_API', true );
	}
}