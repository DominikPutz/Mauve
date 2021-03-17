<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_User_Tags
 */
class UM_User_Tags {


	/**
	 * @var $instance
	 */
	private static $instance;


	/**
	 * @var array
	 */
	public $filters = array();


	/**
	 * @return UM_User_Tags
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_User_Tags constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_user_tags'] = $this;
		add_filter( 'um_call_object_User_Tags', array( &$this, 'get_this' ) );

		$this->admin();
		if ( UM()->is_request( 'admin' ) ) {
			$this->admin_upgrade();
		}
		$this->taxonomies();
		$this->enqueue();
		$this->shortcode();
		$this->member_directory();

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_user_tags_path . 'includes/core/um-user-tags-widget.php';
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );
		add_filter( 'um_excluded_taxonomies', array( &$this, 'excluded_taxonomies' ), 10, 1 );
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
	 * @param $taxes
	 *
	 * @return array
	 */
	function excluded_taxonomies( $taxes ) {
		$taxes[] = 'um_user_tag';
		return $taxes;
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_user_tags_setup'] ) ) {
			UM()->classes['um_user_tags_setup'] = new um_ext\um_user_tags\core\User_Tags_Setup();
		}
		return UM()->classes['um_user_tags_setup'];
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_user_tags_shortcode'] ) ) {
			UM()->classes['um_user_tags_shortcode'] = new um_ext\um_user_tags\core\User_Tags_Shortcode();
		}
		return UM()->classes['um_user_tags_shortcode'];
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_user_tags_member_directory'] ) ) {
			UM()->classes['um_user_tags_member_directory'] = new um_ext\um_user_tags\core\User_Tags_Member_Directory();
		}
		return UM()->classes['um_user_tags_member_directory'];
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_user_tags_enqueue'] ) ) {
			UM()->classes['um_user_tags_enqueue'] = new um_ext\um_user_tags\core\User_Tags_Enqueue();
		}
		return UM()->classes['um_user_tags_enqueue'];
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_user_tags_admin'] ) ) {
			UM()->classes['um_user_tags_admin'] = new um_ext\um_user_tags\core\User_Tags_Admin();
		}
		return UM()->classes['um_user_tags_admin'];
	}


	/**
	 * @return um_ext\um_user_tags\admin\core\Admin_Upgrade()
	 */
	function admin_upgrade() {
		if ( empty( UM()->classes['um_user_tags_admin_upgrade'] ) ) {
			UM()->classes['um_user_tags_admin_upgrade'] = new um_ext\um_user_tags\admin\core\Admin_Upgrade();
		}
		return UM()->classes['um_user_tags_admin_upgrade'];
	}


	/**
	 * @return um_ext\um_user_tags\core\User_Tags_Taxonomies()
	 */
	function taxonomies() {
		if ( empty( UM()->classes['um_user_tags_taxonomies'] ) ) {
			UM()->classes['um_user_tags_taxonomies'] = new um_ext\um_user_tags\core\User_Tags_Taxonomies();
		}
		return UM()->classes['um_user_tags_taxonomies'];
	}


	/**
	 * Init actions/filters
	 */
	function init() {
		require_once um_user_tags_path . 'includes/core/actions/um-user-tags-fields.php';
		require_once um_user_tags_path . 'includes/core/actions/um-user-tags-profile.php';
		require_once um_user_tags_path . 'includes/core/actions/um-user-tags-admin.php';

		require_once um_user_tags_path . 'includes/core/filters/um-user-tags-fields.php';
		require_once um_user_tags_path . 'includes/core/filters/um-user-tags-settings.php';
	}


	/**
	 * @return string
	 */
	function get_base_link() {
		$link = um_get_core_page( 'members' );

		if ( UM()->is_ajax() ) {
			if ( isset( $_REQUEST['action'] ) && sanitize_key( $_REQUEST['action'] ) === 'um_get_members' ) {
				if ( isset( $_REQUEST['post_refferer'] ) ) {
					$link = get_permalink( absint( $_REQUEST['post_refferer'] ) );
				}

				if ( empty( $link ) ) {
					$link = um_get_core_page( 'members' );
				}
			}
		}

		return $link;
	}


	/**
	 * Get user tags by metakey
	 *
	 * @param int $user_id
	 * @param string $metakey
	 *
	 * @return string
	 */
	function get_tags( $user_id, $metakey ) {
		um_fetch_user( $user_id );
		$tags = um_user( $metakey );
		if ( empty( $tags ) ) {
			return '';
		}

		if ( ! UM()->is_ajax() ) {
			wp_enqueue_script( 'um-user-tags' );
			wp_enqueue_style( 'um-user-tags' );
		}

		$link = $this->get_base_link();

		$output = '';
		if ( is_array( $tags ) ) {

			$limit = UM()->options()->get( 'user_tags_max_num' );
			$t_args = compact( 'limit', 'metakey', 'tags', 'link', 'user_id' );
			$output = UM()->get_template( 'tags.php', um_user_tags_plugin, $t_args );

		}

		return $output;
	}


	/**
	 * Get localized terms
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function get_localized_terms( $args ) {

		$args = array_merge( array(
			'taxonomy'      => 'um_user_tag',
			'hide_empty'    => 0,
		), $args );
		$terms = get_terms( $args );

		if ( UM()->external_integrations()->is_wpml_active() ) {
			$language_codes = UM()->external_integrations()->get_languages_codes();

			if ( $language_codes['default'] != $language_codes['current'] ) {
				global $sitepress;

				global $wpdb;

				$lang = explode( '_', $language_codes['default'] );
				$sitepress->switch_lang( $lang[0], true );
				$terms = get_terms( $args );
				$lang = explode( '_', $language_codes['current'] );
				$sitepress->switch_lang( $lang[0], true );

				foreach ( $terms as &$term ) {
					$ret_element_id = $sitepress->get_object_id( $term->term_id, 'um_user_tag', true, $lang[0] );
					if ( $ret_element_id == $term->term_id ) {
						continue;
					}

					$curr_lang_tag = get_term( $ret_element_id, 'um_user_tag' );
					$curr_lang_tag->term_id = $term->term_id;

					$curr_lang_tag->count = $wpdb->get_var( $wpdb->prepare(
						"SELECT count 
						FROM $wpdb->term_taxonomy 
						WHERE term_id = %d",
						$term->term_id
					) );
					$term = $curr_lang_tag;
				}
			}
		}

		return $terms;
	}


	/**
	 * Get localized term
	 *
	 * @param \WP_Term $term
	 *
	 * @return \WP_Term
	 */
	function get_localized_term( $term ) {

		if ( ! UM()->external_integrations()->is_wpml_active() ) {
			return $term;
		}

		$language_codes = UM()->external_integrations()->get_languages_codes();
		if ( $language_codes['default'] == $language_codes['current'] ) {
			return $term;
		}

		global $sitepress;
		$lang = explode( '_', $language_codes['current'] );
		$ret_element_id = $sitepress->get_object_id( $term->term_id, 'um_user_tag', true, $lang[0] );
		if ( $ret_element_id == $term->term_id ) {
			return $term;
		}

		$temp_id = $term->term_id;
		$term = get_term( $ret_element_id, 'um_user_tag' );
		$term->term_id = $temp_id;

		return $term;
	}


	/**
	 * Get localized term by
	 *
	 * @param string|int $tag
	 *
	 * @return \WP_Term
	 */
	function get_localized_term_by( $tag ) {
		if ( is_numeric( $tag ) ) {
			$term = get_term_by( 'id', $tag, 'um_user_tag' );
		} else {
			$term = get_term_by( 'slug', $tag, 'um_user_tag' );

			if ( ! $term ) {
				$term = get_term_by( 'name', $tag, 'um_user_tag' );
			}
		}

		if ( UM()->external_integrations()->is_wpml_active() ) {
			$language_codes = UM()->external_integrations()->get_languages_codes();

			if ( $language_codes['default'] != $language_codes['current'] ) {
				global $sitepress;

				$lang = explode( '_', $language_codes['default'] );
				$sitepress->switch_lang( $lang[0], true );

				if ( is_numeric( $tag ) ) {
					$term = get_term_by( 'id', $tag, 'um_user_tag' );
				} else {
					$term = get_term_by( 'slug', $tag, 'um_user_tag' );

					if ( ! $term ) {
						$term = get_term_by( 'name', $tag, 'um_user_tag' );
					}
				}

				$lang = explode( '_', $language_codes['current'] );
				$sitepress->switch_lang( $lang[0], true );

				$ret_element_id = $sitepress->get_object_id( $term->term_id, 'um_user_tag', true, $lang[0] );
				if ( $ret_element_id == $term->term_id ) {
					return $term;
				}

				$curr_lang_tag = get_term( $ret_element_id, 'um_user_tag' );
				$curr_lang_tag->term_id = $term->term_id;
				$term = $curr_lang_tag;
			}
		}

		return $term;
	}


	/**
	 * UM Tags Widgets init
	 */
	function widgets_init() {
		register_widget( 'um_user_tags_widget' );
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_user_tags', -10, 1 );
function um_init_user_tags() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'User_Tags', true );
	}
}