<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Reviews
 */
class UM_Reviews {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Reviews
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Reviews constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_reviews'] = $this;
		add_filter( 'um_call_object_Reviews', array( &$this, 'get_this' ) );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin_upgrade();
		}

		$this->admin();
		$this->shortcode();
		$this->enqueue();
		$this->member_directory();

		add_action( 'init',  array( &$this, 'create_post_type' ), 2 );
		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );
	
		require_once um_reviews_path . 'includes/core/um-reviews-widget.php';
		add_action( 'widgets_init', array(&$this, 'widgets_init' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		add_filter( 'um_rest_api_get_stats', array( &$this, 'rest_api_get_stats' ), 10, 1 );
		add_filter( 'um_email_templates_path_by_slug', array( &$this, 'email_templates_path_by_slug' ), 10, 1 );

		add_action( 'wp_ajax_um_review_add', array( $this->api(), 'ajax_review_add' ) );
		add_action( 'wp_ajax_um_review_edit', array( $this->api(), 'ajax_review_add' ) );
		add_action( 'wp_ajax_um_review_flag', array( $this->api(), 'ajax_review_flag' ) );
		add_action( 'wp_ajax_nopriv_um_review_flag', array( $this->api(), 'ajax_review_flag' ) );


		add_action( 'wp_ajax_um_review_trash', array( $this->api(), 'ajax_review_trash' ) );
		add_action( 'wp_ajax_um_reviews_reply', array( $this->api(), 'ajax_reply_post' ) );
		add_action( 'wp_ajax_um_reviews_reply_delete', array( $this->api(), 'ajax_reply_delete' ) );
	}


	function email_templates_path_by_slug( $slugs ) {
		$slugs['review_notice'] = um_reviews_path . 'templates/email/';
		$slugs['review_reply_notice'] = um_reviews_path . 'templates/email/';
		return $slugs;
	}


	function rest_api_get_stats( $response ) {
		global $wpdb;

		$total_reviews = absint( $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->prefix}posts 
			WHERE post_status='publish' AND 
				  post_type='um_review'"
		) );
		$response['stats']['total_reviews'] = $total_reviews;

		return $response;
	}


	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}

	/**
	 * @return um_ext\um_reviews\core\Reviews_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_reviews_setup'] ) ) {
			UM()->classes['um_reviews_setup'] = new um_ext\um_reviews\core\Reviews_Setup();
		}
		return UM()->classes['um_reviews_setup'];
	}


	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_reviews\core\Reviews_Main_API()
	 */
	function api() {
		if ( empty( UM()->classes['um_reviews_api'] ) ) {
			UM()->classes['um_reviews_api'] = new um_ext\um_reviews\core\Reviews_Main_API();
		}
		return UM()->classes['um_reviews_api'];
	}


	/**
	 * @return um_ext\um_reviews\core\Reviews_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_reviews_admin'] ) ) {
			UM()->classes['um_reviews_admin'] = new um_ext\um_reviews\core\Reviews_Admin();
		}
		return UM()->classes['um_reviews_admin'];
	}


	/**
	 * @return um_ext\um_reviews\core\Reviews_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_reviews_enqueue'] ) ) {
			UM()->classes['um_reviews_enqueue'] = new um_ext\um_reviews\core\Reviews_Enqueue();
		}
		return UM()->classes['um_reviews_enqueue'];
	}


	/**
	 * @return um_ext\um_reviews\core\Reviews_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_reviews_shortcode'] ) ) {
			UM()->classes['um_reviews_shortcode'] = new um_ext\um_reviews\core\Reviews_Shortcode();
		}
		return UM()->classes['um_reviews_shortcode'];
	}


	/**
	 * @return um_ext\um_reviews\core\Reviews_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_reviews_member_directory'] ) ) {
			UM()->classes['um_reviews_member_directory'] = new um_ext\um_reviews\core\Reviews_Member_Directory();
		}
		return UM()->classes['um_reviews_member_directory'];
	}


	/**
	 * @return um_ext\um_reviews\admin\core\Admin_Upgrade()
	 */
	function admin_upgrade() {
		if ( empty( UM()->classes['um_reviews_admin_upgrade'] ) ) {
			UM()->classes['um_reviews_admin_upgrade'] = new um_ext\um_reviews\admin\core\Admin_Upgrade();
		}
		return UM()->classes['um_reviews_admin_upgrade'];
	}


	/***
	 ***	@creates a post type
	 ***/
	function create_post_type() {

		register_post_type( 'um_review', array(
				'labels' => array(
					'name' => __( 'User Reviews' ),
					'singular_name' => __( 'Review' ),
					'add_new' => __( 'Add New Review' ),
					'add_new_item' => __('Add New Review' ),
					'edit_item' => __('Edit Review'),
					'not_found' => __('No user reviews have been submitted yet'),
					'not_found_in_trash' => __('Nothing found in Trash'),
					'search_items' => __('Search Reviews')
				),
				'show_ui' => true,
				'show_in_menu' => false,
				'public' => false,
				'supports' => array('title', 'editor')
			)
		);

	}


	/***
	***	@Init
	***/
	function init() {
		// Actions
		require_once um_reviews_path . 'includes/core/actions/um-reviews-tabs.php';
		require_once um_reviews_path . 'includes/core/actions/um-reviews-trash.php';
		require_once um_reviews_path . 'includes/core/actions/um-reviews-admin.php';
		require_once um_reviews_path . 'includes/core/actions/um-reviews-controls.php';
		require_once um_reviews_path . 'includes/core/actions/um-reviews-members.php';
		
		// Filters
		require_once um_reviews_path . 'includes/core/filters/um-reviews-tabs.php';
		require_once um_reviews_path . 'includes/core/filters/um-reviews-settings.php';
		require_once um_reviews_path . 'includes/core/filters/um-reviews-permissions.php';
		require_once um_reviews_path . 'includes/core/filters/um-reviews-fields.php';

	}


	function widgets_init() {
		
		register_widget( 'um_reviews_top_rated' );
		register_widget( 'um_reviews_most_rated' );
		register_widget( 'um_reviews_lowest_rated' );

	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_reviews', -10, 1 );
function um_init_reviews() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Reviews', true );
	}
}