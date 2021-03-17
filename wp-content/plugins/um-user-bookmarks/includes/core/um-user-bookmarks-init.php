<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'UM_User_Bookmarks' ) ) {


	/**
	 * Class UM_User_Bookmarks
	 */
	class UM_User_Bookmarks extends UM_User_Bookmarks_Functions {


		/**
		 * @var
		 */
		private static $instance;


		/**
		 * @return UM_User_Bookmarks
		 */
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->_um_user_bookmarks_construct();
			}

			return self::$instance;
		}


		/**
		 * UM_User_Bookmarks constructor.
		 *
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * UM_User_Bookmarks constructor.
		 */
		function _um_user_bookmarks_construct() {

			add_filter( 'um_call_object_User_Bookmarks', array( &$this, 'get_this' ) );
			add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

			$this->common();

			if ( UM()->is_request( 'ajax' ) ) {
				$this->admin();
				$this->ajax();
			} elseif ( UM()->is_request( 'admin' ) ) {
				$this->admin();
				$this->ajax();
				$this->metabox();
			} /*elseif ( UM()->is_request( 'frontend' ) ) {

			}*/

			$this->profile();
			$this->privacy();
			$this->shortcode();
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
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Setup()
		 */
		function setup() {
			if ( empty( UM()->classes['um_user_bookmarks_setup'] ) ) {
				UM()->classes['um_user_bookmarks_setup'] = new um_ext\um_user_bookmarks\core\Bookmark_Setup();
			}

			return UM()->classes['um_user_bookmarks_setup'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\admin\Admin()
		 */
		function admin() {
			if ( empty( UM()->classes['um_user_bookmarks_admin'] ) ) {
				UM()->classes['um_user_bookmarks_admin'] = new um_ext\um_user_bookmarks\admin\Admin();
			}

			return UM()->classes['um_user_bookmarks_admin'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Common()
		 */
		function common() {
			if ( empty( UM()->classes['um_user_bookmarks_common'] ) ) {
				UM()->classes['um_user_bookmarks_common'] = new um_ext\um_user_bookmarks\core\Bookmark_Common();
			}

			return UM()->classes['um_user_bookmarks_common'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Privacy()
		 */
		function privacy() {
			if ( empty( UM()->classes['um_user_bookmarks_privacy'] ) ) {
				UM()->classes['um_user_bookmarks_privacy'] = new um_ext\um_user_bookmarks\core\Bookmark_Privacy();
			}

			return UM()->classes['um_user_bookmarks_privacy'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Profile()
		 */
		function profile() {
			if ( empty( UM()->classes['um_user_bookmarks_profile'] ) ) {
				UM()->classes['um_user_bookmarks_profile'] = new um_ext\um_user_bookmarks\core\Bookmark_Profile();
			}

			return UM()->classes['um_user_bookmarks_profile'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Ajax()
		 */
		function ajax() {
			if ( empty( UM()->classes['um_user_bookmarks_ajax'] ) ) {
				UM()->classes['um_user_bookmarks_ajax'] = new um_ext\um_user_bookmarks\core\Bookmark_Ajax();
			}

			return UM()->classes['um_user_bookmarks_ajax'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Metabox()
		 */
		function metabox() {
			if ( empty( UM()->classes['um_user_bookmarks_metabox'] ) ) {
				UM()->classes['um_user_bookmarks_metabox'] = new um_ext\um_user_bookmarks\core\Bookmark_Metabox();
			}

			return UM()->classes['um_user_bookmarks_metabox'];
		}


		/**
		 * @return um_ext\um_user_bookmarks\core\Bookmark_Shortcode()
		 */
		function shortcode() {
			if ( empty( UM()->classes['um_user_bookmarks_shortcode'] ) ) {
				UM()->classes['um_user_bookmarks_shortcode'] = new um_ext\um_user_bookmarks\core\Bookmark_Shortcode();
			}

			return UM()->classes['um_user_bookmarks_shortcode'];
		}
	}

}
//create class var
add_action( 'plugins_loaded', 'um_init_user_bookmarks', -10, 1 );
function um_init_user_bookmarks() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'User_Bookmarks', true );
	}
}