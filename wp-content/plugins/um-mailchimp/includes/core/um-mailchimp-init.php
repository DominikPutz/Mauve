<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The main class of the "Ultimate Member - MailChimp" plugin
 * 
 * @example UM()->classes['Mailchimp']
 * @example UM()->Mailchimp()
 */
class UM_Mailchimp {


	/**
	 * Class object
	 * @var UM_Mailchimp
	 */
	private static $instance;


	/**
	 * @return UM_Mailchimp
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Class constructor
	 */
	public function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_mailchimp'] = $this;

		add_filter( 'um_call_object_Mailchimp', array( &$this, 'get_this' ) );
		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		$this->api();
		$this->ajax();

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		add_action( 'init', array( &$this, 'create_taxonomies' ), 2 );
		add_action( 'plugins_loaded', array( &$this, 'init' ), 1 );
	}


	/**
	 * Get class object
	 *
	 * @return $this
	 */
	public function get_this() {
		return $this;
	}


	/**
	 * Filter settings
	 *
	 * @param array $defaults
	 * @return array
	 */
	public function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * Create a mailchimp post type
	 */
	public function create_taxonomies() {

		register_post_type( 'um_mailchimp', array(
			'labels'        => array(
				'name'                  => __( 'MailChimp', 'um-mailchimp' ),
				'singular_name'         => __( 'MailChimp', 'um-mailchimp' ),
				'add_new'               => __( 'Add New audience', 'um-mailchimp' ),
				'add_new_item'          => __( 'Add New audience', 'um-mailchimp' ),
				'edit_item'             => __( 'Edit audience', 'um-mailchimp' ),
				'not_found'             => __( 'You did not create any MailChimp audiences yet', 'um-mailchimp' ),
				'not_found_in_trash'    => __( 'Nothing found in Trash', 'um-mailchimp' ),
				'search_items'          => __( 'Search MailChimp audiences', 'um-mailchimp' )
			),
			'show_ui'       => true,
			'show_in_menu'  => false,
			'public'        => false,
			'supports'      => array( 'title' )
		) );

	}


	/**
	 * Init
	 */
	public function init() {

		//libs
		if ( ! class_exists('UM_MailChimp_V3') ) {
			require_once um_mailchimp_path . 'includes/lib/um-mailchimp-api-v3.php';
		}

		if ( ! class_exists('UM_MailChimp_Batch') ) {
			require_once um_mailchimp_path . 'includes/lib/um-mailchimp-batch.php';
		}

		require_once um_mailchimp_path . 'includes/core/actions/um-mailchimp-account.php';
		require_once um_mailchimp_path . 'includes/core/actions/um-mailchimp-fields.php';

		require_once um_mailchimp_path . 'includes/core/filters/um-mailchimp-account.php';
		require_once um_mailchimp_path . 'includes/core/filters/um-mailchimp-fields.php';

	}





	/**
	 * Admin
	 * @return um_ext\um_mailchimp\admin\core\Admin()
	 */
	public function admin() {
		if ( empty( UM()->classes['um_mailchimp_admin'] ) ) {
			UM()->classes['um_mailchimp_admin'] = new um_ext\um_mailchimp\admin\core\Admin();
		}
		return UM()->classes['um_mailchimp_admin'];
	}


	/**
	 * Main functionality
	 *
	 * @return um_ext\um_mailchimp\core\Api()
	 */
	public function api() {
		if ( empty( UM()->classes['um_mailchimp_main_api'] ) ) {
			UM()->classes['um_mailchimp_main_api'] = new um_ext\um_mailchimp\core\Api();
		}
		return UM()->classes['um_mailchimp_main_api'];
	}


	/**
	 * AJAX handlers
	 * @return um_ext\um_mailchimp\core\Ajax()
	 */
	public function ajax() {
		if ( empty( UM()->classes['um_mailchimp_ajax'] ) ) {
			UM()->classes['um_mailchimp_ajax'] = new um_ext\um_mailchimp\core\Ajax();
		}
		return UM()->classes['um_mailchimp_ajax'];
	}


	/**
	 * Log requests to the MailChimp API
	 *
	 * @return um_ext\um_mailchimp\core\Log()
	 */
	public function log() {
		if ( empty( UM()->classes['um_mailchimp_log'] ) ) {
			UM()->classes['um_mailchimp_log'] = new um_ext\um_mailchimp\core\Log();
		}
		return UM()->classes['um_mailchimp_log'];
	}


	/**
	 * Setup
	 * @return um_ext\um_mailchimp\core\Setup()
	 */
	public function setup() {
		if ( empty( UM()->classes['um_mailchimp_setup'] ) ) {
			UM()->classes['um_mailchimp_setup'] = new um_ext\um_mailchimp\core\Setup();
		}
		return UM()->classes['um_mailchimp_setup'];
	}
}


//create class var
add_action( 'plugins_loaded', 'um_init_mailchimp', -10, 1 );
function um_init_mailchimp() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Mailchimp', true );
	}
}