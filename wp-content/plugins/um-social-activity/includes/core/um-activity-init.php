<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class UM_Activity_API {
    private static $instance;

    static public function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	function __construct() {

        // Global for backwards compatibility.
        $GLOBALS['um_activity'] = $this;
        add_filter( 'um_call_object_Activity_API', array( &$this, 'get_this' ) );

        $this->api();
        $this->admin();
        $this->shortcode();
        $this->enqueue();

        add_action( 'init', array( &$this, 'create_post_type' ), 2 );
		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_activity_path . 'includes/core/um-activity-widget.php';
		add_action( 'widgets_init', array(&$this, 'widgets_init' ) );

        add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );
        add_filter( 'um_excluded_taxonomies', array( &$this, 'excluded_taxonomies' ), 10, 1 );


		add_action( 'wp_ajax_um_activity_load_wall', array( $this->api(), 'ajax_load_wall' ) );
		add_action( 'wp_ajax_nopriv_um_activity_load_wall', array( $this->api(), 'ajax_load_wall' ) );
		add_action( 'wp_ajax_um_activity_get_post_likes', array( $this->api(), 'ajax_get_post_likes' ) );
		add_action( 'wp_ajax_nopriv_um_activity_get_post_likes', array( $this->api(), 'ajax_get_post_likes' ) );
		add_action( 'wp_ajax_um_activity_get_comment_likes', array( $this->api(), 'ajax_get_comment_likes' ) );
		add_action( 'wp_ajax_nopriv_um_activity_get_comment_likes', array( $this->api(), 'ajax_get_comment_likes' ) );
		add_action( 'wp_ajax_um_activity_load_more_comments', array( $this->api(), 'ajax_load_more_comments' ) );
		add_action( 'wp_ajax_nopriv_um_activity_load_more_comments', array( $this->api(), 'ajax_load_more_comments' ) );
		add_action( 'wp_ajax_um_activity_load_more_replies', array( $this->api(), 'ajax_load_more_replies' ) );
		add_action( 'wp_ajax_nopriv_um_activity_load_more_replies', array( $this->api(), 'ajax_load_more_replies' ) );

		add_action( 'wp_ajax_um_activity_get_user_suggestions', array( $this->api(), 'ajax_get_user_suggestions' ) );
		add_action( 'wp_ajax_um_activity_remove_post', array( $this->api(), 'ajax_remove_post' ) );
		add_action( 'wp_ajax_um_activity_remove_comment', array( $this->api(), 'ajax_remove_comment' ) );
		add_action( 'wp_ajax_um_activity_hide_comment', array( $this->api(), 'ajax_hide_comment' ) );
		add_action( 'wp_ajax_um_activity_unhide_comment', array( $this->api(), 'ajax_unhide_comment' ) );
		add_action( 'wp_ajax_um_activity_report_post', array( $this->api(), 'ajax_report_post' ) );
		add_action( 'wp_ajax_um_activity_unreport_post', array( $this->api(), 'ajax_unreport_post' ) );
		add_action( 'wp_ajax_um_activity_like_comment', array( $this->api(), 'ajax_like_comment' ) );
		add_action( 'wp_ajax_um_activity_unlike_comment', array( $this->api(), 'ajax_unlike_comment' ) );
		add_action( 'wp_ajax_um_activity_like_post', array( $this->api(), 'ajax_like_post' ) );
		add_action( 'wp_ajax_um_activity_unlike_post', array( $this->api(), 'ajax_unlike_post' ) );
		add_action( 'wp_ajax_um_activity_wall_comment', array( $this->api(), 'ajax_wall_comment' ) );
		add_action( 'wp_ajax_um_activity_publish', array( $this->api(), 'ajax_activity_publish' ) );

		add_action( 'wp_ajax_um_get_activity_post', array( $this->api(), 'ajax_get_activity_post' ) );
		add_action( 'wp_ajax_um_get_activity_comment', array( $this->api(), 'ajax_get_activity_comment' ) );

		add_filter( 'um_change_upload_user_path', array( &$this, 'change_upload_user' ), 10, 3 );

		add_filter( 'query_vars', array(&$this, 'query_vars'), 10, 1 );
		add_filter( 'rewrite_rules_array', array( &$this, '_add_rewrite_rules' ), 10, 1 );

		add_action( 'template_redirect', array( &$this, 'download_routing' ), 8 );
	}


	/**
	 * Change uploader dir
	 *
	 * @param array $paths
	 * @param string $field_key
	 * @param string $upload_type
	 *
	 * @return array
	 */
	function change_upload_user( $paths, $field_key, $upload_type ) {
		if ( 'wall_img_upload' == $field_key && 'image' == $upload_type ) {
			$paths = array(
				UM()->uploader()->upload_baseurl . UM()->uploader()->temp_upload_dir,
				UM()->uploader()->upload_basedir . UM()->uploader()->temp_upload_dir
			);
		}

		return $paths;
	}


	/**
	 * Modify global query vars
	 *
	 * @param $public_query_vars
	 *
	 * @return array
	 */
	function query_vars( $public_query_vars ) {
		$public_query_vars[] = 'um_post';
		$public_query_vars[] = 'um_author';

		return $public_query_vars;
	}


	/**
	 * Add UM rewrite rules
	 *
	 * @param $rules
	 *
	 * @return array
	 */
	function _add_rewrite_rules( $rules ) {
		$newrules = array();

		$newrules['um-activity-download/([^/]+)/([^/]+)/([^/]+)/?$'] = 'index.php?um_action=um-activity-download&um_post=$matches[1]&um_author=$matches[2]&um_verify=$matches[3]';

		return $newrules + $rules;
	}


	/**
	 * @return bool
	 */
	function download_routing() {
		if ( 'um-activity-download' !== get_query_var( 'um_action' ) ) {
			return false;
		}

		$um_post = get_query_var( 'um_post' );
		if ( empty( $um_post ) ) {
			return false;
		}

		$post_id = get_query_var( 'um_post' );
		$post = get_post( $post_id );
		if ( empty( $post ) || is_wp_error( $post ) ) {
			return false;
		}

		$uri = get_post_meta( $post_id, '_photo', true );
		if ( ! $uri ) {
			return false;
		}

		$um_author = get_query_var( 'um_author' );
		if ( empty( $um_author ) ) {
			return false;
		}
		$author_id = get_query_var( 'um_author' );
		$user = get_userdata( $author_id );

		if ( empty( $user ) || is_wp_error( $user ) ) {
			return false;
		}

		$verify = get_query_var( 'um_verify' );
		if ( empty( $verify ) ||
		     ! wp_verify_nonce( $verify, $author_id . $post_id . 'um-download-nonce' ) ) {
			return false;
		}

		$uri = wp_basename( $uri );
		$user_base_dir = UM()->uploader()->get_upload_user_base_dir( $author_id );
		$file_path = $user_base_dir . DIRECTORY_SEPARATOR . $uri;
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		$size = filesize( $file_path );
		$file_info = get_post_meta( $post_id, "_photo_metadata", true );
		$originalname = $file_info['original_name'];
		$type = $file_info['type'];

		header('Content-Description: File Transfer');
		header('Content-Type: ' . $type );
		header('Content-Disposition: inline; filename="' . $originalname . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $size);

		$levels = ob_get_level();
		for ( $i = 0; $i < $levels; $i++ ) {
			@ob_end_clean();
		}

		readfile( $file_path );
		exit;
	}


    function default_settings( $defaults ) {
        $defaults = array_merge( $defaults, $this->setup()->settings_defaults );
        return $defaults;
    }

    function excluded_taxonomies( $taxes ) {
        $taxes[] = 'um_hashtag';
        return $taxes;
    }


    /**
     * @return um_ext\um_social_activity\core\Activity_Setup()
     */
    function setup() {
        if ( empty( UM()->classes['um_activity_setup'] ) ) {
            UM()->classes['um_activity_setup'] = new um_ext\um_social_activity\core\Activity_Setup();
        }
        return UM()->classes['um_activity_setup'];
    }


    function get_this() {
        return $this;
    }


    /***
     ***	@creates a post type
     ***/
    function create_post_type() {

        register_post_type( 'um_activity', array(
                'labels' => array(
                    'name' => __( 'Social Activity' ),
                    'singular_name' => __( 'Social Activity' ),
                    'add_new' => __( 'Add New Post' ),
                    'add_new_item' => __('Add New Post' ),
                    'edit_item' => __('Edit Post'),
                    'not_found' => __('No wall posts have been added yet'),
                    'not_found_in_trash' => __('Nothing found in Trash'),
                    'search_items' => __('Search Posts')
                ),
                'public' => false,
                'supports' => array('editor'),
                'taxonomies' => array('um_hashtag'),
                'show_ui' => true,
                'show_in_menu' => false,
                'capability_type' => 'page'
            )
        );

        // Add new taxonomy, NOT hierarchical (like tags)
        $labels = array(
            'name'                       => _x( 'Hashtags', 'taxonomy general name' ),
            'singular_name'              => _x( 'Hashtag', 'taxonomy singular name' ),
            'search_items'               => __( 'Search Hashtags' ),
            'popular_items'              => __( 'Popular Hashtags' ),
            'all_items'                  => __( 'All Hashtags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Hashtag' ),
            'update_item'                => __( 'Update Hashtag' ),
            'add_new_item'               => __( 'Add New Hashtag' ),
            'new_item_name'              => __( 'New Hashtag Name' ),
            'separate_items_with_commas' => __( 'Separate hashtags with commas' ),
            'add_or_remove_items'        => __( 'Add or remove hashtags' ),
            'choose_from_most_used'      => __( 'Choose from the most used hashtags' ),
            'not_found'                  => __( 'No hashtags found.' ),
            'menu_name'                  => __( 'Hashtags' ),
        );

        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => false,
            'rewrite'               => array( 'slug' => 'hashtag' ),
            'show_in_menu' 			=> false,
        );

        register_taxonomy( 'um_hashtag', 'um_activity', $args );

    }


    /**
     * @return um_ext\um_social_activity\core\Activity_Main_API()
     */
    function api() {
        if ( empty( UM()->classes['um_activity_api'] ) ) {
            UM()->classes['um_activity_api'] = new um_ext\um_social_activity\core\Activity_Main_API();
        }
        return UM()->classes['um_activity_api'];
    }


    /**
     * @return um_ext\um_social_activity\core\Activity_Shortcode()
     */
    function shortcode() {
        if ( empty( UM()->classes['um_activity_shortcode'] ) ) {
            UM()->classes['um_activity_shortcode'] = new um_ext\um_social_activity\core\Activity_Shortcode();
        }
        return UM()->classes['um_activity_shortcode'];
    }


    /**
     * @return um_ext\um_social_activity\core\Activity_Enqueue()
     */
    function enqueue() {
        if ( empty( UM()->classes['um_activity_enqueue'] ) ) {
            UM()->classes['um_activity_enqueue'] = new um_ext\um_social_activity\core\Activity_Enqueue();
        }
        return UM()->classes['um_activity_enqueue'];
    }


    /**
     * @return um_ext\um_social_activity\core\Activity_Admin()
     */
    function admin() {
        if ( empty( UM()->classes['um_activity_admin'] ) ) {
            UM()->classes['um_activity_admin'] = new um_ext\um_social_activity\core\Activity_Admin();
        }
        return UM()->classes['um_activity_admin'];
    }


	/***
	***	@Init
	***/
	function init() {

		// Actions
		require_once um_activity_path . 'includes/core/actions/um-activity-admin.php';
		require_once um_activity_path . 'includes/core/actions/um-activity-webnotification.php';
		require_once um_activity_path . 'includes/core/actions/um-activity-actions.php';
		require_once um_activity_path . 'includes/core/actions/um-activity-footer.php';

		// Filters
		require_once um_activity_path . 'includes/core/filters/um-activity-rss.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-settings.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-privacy.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-comments.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-mentions.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-integrate-pressthis.php';
		require_once um_activity_path . 'includes/core/filters/um-activity-oembed.php';

	}

	function widgets_init() {
		register_widget( 'um_activity_trending_tags' );
	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_activity', -10, 1 );
function um_init_activity() {
    if ( function_exists( 'UM' ) ) {
        UM()->set_class( 'Activity_API', true );
    }
}