<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class UM_Notices
 */
class UM_Notices {


	/**
	 * @var
	 */
	var $shortcodes;


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Notices
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Notices constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_notices'] = $this;
		add_filter( 'um_call_object_Notices_API', array( &$this, 'get_this' ) );

		$this->query();

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
			$this->metabox();
		}

		if ( UM()->is_request( 'frontend' ) ) {
			$this->enqueue();
			$this->shortcode();
		}

		add_action( 'init',  array( &$this, 'create_cpt' ), 2 );
		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		add_action( 'um_delete_user',  array( $this, 'clear_notices' ), 10, 1 );
		add_action( 'wp_ajax_um_notices_mark_seen',  array( $this, 'ajax_mark_notice_seen' ) );
		add_action( 'wp_ajax_nopriv_um_notices_mark_seen',  array( $this, 'ajax_mark_notice_seen' ) );
	}


	/**
	 * @param $user_id
	 */
	function clear_notices( $user_id ) {
		$posts = get_posts( array(
			'post_type'         => 'um_notice',
			'posts_per_page'    => -1,
			'fields'            => 'ids',
		) );

		foreach ( $posts as $post_id ) {
			$users = get_post_meta( $post_id, '_users', true );
			if ( $users && is_array( $users ) && in_array( $user_id, $users ) ) {
				$user_key = array_search( $user_id, $users );
				unset( $users[ $user_key ] );

				update_post_meta( $post_id, '_users', $users );
			}
		}
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
	 * @return um_ext\um_notices\core\Notices_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_notices_setup'] ) ) {
			UM()->classes['um_notices_setup'] = new um_ext\um_notices\core\Notices_Setup();
		}
		return UM()->classes['um_notices_setup'];
	}


	/**
	 *
	 */
	function ajax_mark_notice_seen() {
		UM()->check_ajax_nonce();

		/**
		 * @var $user_id
		 * @var $notice_id
		 */
		extract( $_REQUEST );

		if ( $user_id > 0 && $notice_id > 0 ) { // member
			$users = get_post_meta( $notice_id, '_users', true );

			if ( is_array( $users ) ) {
				$users[] = $user_id;
			} else {
				$users = array();
				$users[] = $user_id;
			}

			update_post_meta( $notice_id, '_users', $users );
		}

		// register this notice in a cookie anyway
		setcookie( 'um_notice_seen_' . $notice_id, true, time() + ( 86400 * 7 ), '/' );
		wp_send_json_success();
	}


	/**
	 * @return um_ext\um_notices\core\Notices_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_notices_admin'] ) ) {
			UM()->classes['um_notices_admin'] = new um_ext\um_notices\core\Notices_Admin();
		}
		return UM()->classes['um_notices_admin'];
	}


	/**
	 * @return um_ext\um_notices\core\Notices_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_notices_enqueue'] ) ) {
			UM()->classes['um_notices_enqueue'] = new um_ext\um_notices\core\Notices_Enqueue();
		}
		return UM()->classes['um_notices_enqueue'];
	}


	/**
	 * @return um_ext\um_notices\core\Notices_Metabox()
	 */
	function metabox() {
		if ( empty( UM()->classes['um_notices_metabox'] ) ) {
			UM()->classes['um_notices_metabox'] = new um_ext\um_notices\core\Notices_Metabox();
		}
		return UM()->classes['um_notices_metabox'];
	}


	/**
	 * @return um_ext\um_notices\core\Notices_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_notices_shortcode'] ) ) {
			UM()->classes['um_notices_shortcode'] = new um_ext\um_notices\core\Notices_Shortcode();
		}
		return UM()->classes['um_notices_shortcode'];
	}


	/**
	 * @return um_ext\um_notices\core\Notices_Query()
	 */
	function query() {
		if ( empty( UM()->classes['um_notices_query'] ) ) {
			UM()->classes['um_notices_query'] = new um_ext\um_notices\core\Notices_Query();
		}
		return UM()->classes['um_notices_query'];
	}


	/**
	 * Init
	 */
	function init() {
		require_once um_notices_path . 'includes/core/filters/um-notices-settings.php';
	}


	/**
	 * creates needed cpt
	 */
	function create_cpt() {
		register_post_type( 'um_notice', array(
				'labels'        => array(
					'name'                  => __( 'Notices', 'um-notices' ),
					'singular_name'         => __( 'Notice', 'um-notices' ),
					'add_new'               => __( 'Add New Notice', 'um-notices' ),
					'add_new_item'          => __('Add New Notice', 'um-notices' ),
					'edit_item'             => __( 'Edit Notice', 'um-notices' ),
					'not_found'             => __( 'You did not create any notices yet', 'um-notices' ),
					'not_found_in_trash'    => __( 'Nothing found in Trash', 'um-notices' ),
					'search_items'          => __( 'Search Notices', 'um-notices' )
				),
				'show_ui'       => true,
				'show_in_menu'  => false,
				'public'        => false,
				'supports'      => array( 'title', 'editor' ),
				'capability_type' => 'page'
			)
		);
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_notices', -10, 1 );
function um_init_notices() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Notices', true );
	}
}