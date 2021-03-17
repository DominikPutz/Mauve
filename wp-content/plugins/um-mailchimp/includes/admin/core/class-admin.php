<?php
namespace um_ext\um_mailchimp\admin\core;

// Exit if accessed directly.
if( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin side functionality
 *
 * @example UM()->classes['um_mailchimp_admin']
 * @example UM()->Mailchimp()->admin()
 */
class Admin {

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->pagehook = 'toplevel_page_ultimatemember';

		// scripts & styles
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), 9 );

		// menu
		add_action( 'um_extend_admin_menu', array( &$this, 'admin_menu' ), 150 );

		// notices
		add_action( 'admin_notices', array( &$this, 'admin_notices' ), 1 );
		add_action( 'um_admin_do_action__um_hide_mailchimp_notice', array( &$this, 'admin_notices_hide_mailchimp_notice' ) );

		// audience table
		add_filter( 'manage_edit-um_mailchimp_columns', array( &$this, 'manage_edit_um_mailchimp_columns' ) );
		add_action( 'manage_um_mailchimp_posts_custom_column', array( &$this, 'manage_um_mailchimp_posts_custom_column' ), 10, 3 );

		// metabox
		add_action( 'load-post.php', array( &$this, 'add_metabox' ), 9 );
		add_action( 'load-post-new.php', array( &$this, 'add_metabox' ), 9 );

		// dashboard
		$this->dashboard();
		// settings
		$this->settings();
		// upgrade process
		$this->upgrade();
	}

	/**
	 * Dashboard
	 * @return Dashboard()
	 */
	public function dashboard() {
		if( empty( UM()->classes[ 'um_mailchimp_dashboard' ] ) ) {
			UM()->classes[ 'um_mailchimp_dashboard' ] = new Dashboard();
		}
		return UM()->classes[ 'um_mailchimp_dashboard' ];
	}


	/**
	 * Settings
	 *
	 * @return Settings()
	 */
	public function settings() {
		if( empty( UM()->classes['um_mailchimp_admin_settings'] ) ) {
			UM()->classes['um_mailchimp_admin_settings'] = new Settings();
		}
		return UM()->classes['um_mailchimp_admin_settings'];
	}


	/**
	 * Upgrade
	 *
	 * @return Upgrade()
	 */
	public function upgrade() {
		if ( empty( UM()->classes['um_mailchimp_admin_upgrade'] ) ) {
			UM()->classes['um_mailchimp_admin_upgrade'] = new Upgrade();
		}
		return UM()->classes['um_mailchimp_admin_upgrade'];
	}



	/**
	 * Admin Styles
	 */
	public function admin_enqueue_scripts() {
		wp_register_style( 'um_admin_mailchimp', um_mailchimp_url . 'assets/css/um-admin-mailchimp.css' );
		wp_enqueue_style( 'um_admin_mailchimp' );

		wp_enqueue_script( 'um_admin_mailchimp', um_mailchimp_url . 'assets/js/um-mailchimp-admin.js', array( 'jquery', 'wp-util', 'um_admin_global' ), um_mailchimp_version, true );
		wp_enqueue_script( 'um_admin_mailchimp' );
	}

	/**
	 * Admin menu
	 */
	public function admin_menu() {

		add_submenu_page( UM()->admin_menu()->slug, __( 'MailChimp', 'um-mailchimp' ), __( 'MailChimp', 'um-mailchimp' ), 'manage_options', 'edit.php?post_type=um_mailchimp', '' );
	}

	/**
	 * Show main notices
	 */
	public function admin_notices() {
		$hide_notice = get_option( 'um_hide_mailchimp_notice' );

		if( $hide_notice ) {
			return;
		}

		$hide_link = add_query_arg( 'um_adm_action', 'um_hide_mailchimp_notice' );
		$key = UM()->options()->get( 'mailchimp_api' );

		if( !$key ) {
			echo '<div class="updated um-admin-notice"><p>';
			printf( __( 'You must add your <strong>MailChimp API</strong> key before connecting your newsletter audiences. <a href="%s">Hide this notice</a>', 'um-mailchimp' ), $hide_link );
			echo '</p><p><a href="' . admin_url( 'admin.php?page=um_options&tab=extensions&section=mailchimp' ) . '" class="button button-primary">' . __( 'Setup MailChimp API', 'um-mailchimp' ) . '</a></p></div>';
		}
	}

	/**
	 * Hide notice
	 *
	 * @param $action
	 */
	public function admin_notices_hide_mailchimp_notice( $action ) {
		if( !is_admin() || !current_user_can( 'manage_options' ) ) {
			die();
		}
		update_option( $action, 1 );
		exit( wp_redirect( remove_query_arg( 'um_adm_action' ) ) );
	}

	/**
	 * Init the metaboxes
	 *
	 * @global WP_User $current_screen
	 */
	function add_metabox() {
		global $current_screen;

		if( $current_screen->id == 'um_mailchimp' ) {
			add_action( 'add_meta_boxes', array( &$this, 'add_metabox_form' ), 1 );
			add_action( 'save_post_um_mailchimp', array( &$this, 'save_metabox_form' ), 10, 2 );
		}
	}

	/**
	 * Add form metabox
	 */
	function add_metabox_form() {

		add_meta_box(
				'um-admin-mailchimp-list', __( 'Setup audience', 'um-mailchimp' ), array( &$this, 'load_metabox_form' ), 'um_mailchimp', 'normal', 'default'
		);

		add_meta_box(
				'um-admin-mailchimp-merge', __( 'Merge User Meta', 'um-mailchimp' ), array( &$this, 'load_metabox_form' ), 'um_mailchimp', 'normal', 'default'
		);

		add_meta_box(
				'um-admin-mailchimp-test-connection', __( 'Testing connection with Mailchimp server', 'um-mailchimp' ), array( &$this, 'load_metabox_form' ), 'um_mailchimp', 'normal', 'default'
		);
	}

	/**
	 * Load a form metabox
	 *
	 * @param $object
	 * @param $box
	 */
	function load_metabox_form( $object, $box ) {
		$post_id = get_the_ID();
		$box[ 'id' ] = str_replace( 'um-admin-mailchimp-', '', $box[ 'id' ] );
		include_once um_mailchimp_path . 'includes/admin/templates/' . $box[ 'id' ] . '.php';
		wp_nonce_field( basename( __FILE__ ), 'um_admin_metabox_mailchimp_form_nonce' );
	}

	/**
	 * Save form metabox
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed
	 */
	function save_metabox_form( $post_id, $post ) {
		// validate nonce
		if ( ! isset( $_POST['um_admin_metabox_mailchimp_form_nonce'] ) || ! wp_verify_nonce( $_POST['um_admin_metabox_mailchimp_form_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// validate user
		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		$list_id = get_post_meta( $post_id, '_um_list', true );
		if ( $list_id ) {
			foreach ( UM()->Mailchimp()->api()->mc_get_merge_fields( $list_id ) as $data ) {
				if ( isset( $data['required'] ) && $data['required'] && empty( $_POST['mailchimp']['_um_merge'][ $data['tag'] ] ) ) {
					return $post_id;
				}
			}
		}

		foreach ( $_POST['mailchimp'] as $k => $v ) {
			if ( strstr( $k, '_um_' ) ) {
				update_post_meta( $post_id, $k, $v );
			}
		}

		return $post_id;
	}

	/**
	 * Custom columns
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	function manage_edit_um_mailchimp_columns( $columns ) {
		$new_columns[ 'cb' ] = '<input type="checkbox" />';
		$new_columns[ 'title' ] = __( 'Title', 'um-mailchimp' );
		$new_columns[ 'connection' ] = __( 'Connection', 'um-mailchimp' );
		$new_columns[ 'status' ] = __( 'Status', 'um-mailchimp' );
		$new_columns[ 'reg_status' ] = __( 'Automatic Signup', 'um-mailchimp' );
		$new_columns[ 'list_id' ] = __( 'audience ID', 'um-mailchimp' );
		$new_columns[ 'subscribers' ] = __( 'Subscribers', 'um-mailchimp' );
		$new_columns[ 'available_to' ] = __( 'Roles', 'um-mailchimp' );

		return $new_columns;
	}

	/**
	 * Display custom columns
	 *
	 * @param $column_name
	 * @param $id
	 */
	function manage_um_mailchimp_posts_custom_column( $column_name, $id ) {
		switch( $column_name ) {

			case 'connection':
				$remote_lists = UM()->Mailchimp()->api()->get_lists();
				$list_id = get_post_meta( $id, '_um_list', true );
				if( isset( $remote_lists[ $list_id ] ) ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="' . esc_attr__( 'audience found', 'um-mailchimp' ) . '"><i class="um-faicon-check"></i></span>';
				}
				else {
					delete_post_meta( $id, '_um_status' );
					echo '<span class="um-adm-ico inactive um-admin-tipsy-n" title="' . esc_attr__( 'Unknown audience', 'um-mailchimp' ) . '"><i class="um-faicon-remove"></i></span>';
				}
				break;

			case 'status':
				$status = get_post_meta( $id, '_um_status', true );
				if( $status ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="' . esc_attr__( 'Active', 'um-mailchimp' ) . '"><i class="um-faicon-check"></i></span>';
				}
				else {
					echo '<span class="um-adm-ico inactive um-admin-tipsy-n" title="' . esc_attr__( 'Inactive', 'um-mailchimp' ) . '"><i class="um-faicon-remove"></i></span>';
				}
				break;

			case 'reg_status':
				$status = get_post_meta( $id, '_um_reg_status', true );
				if( $status ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="' . esc_attr__( 'Active', 'um-mailchimp' ) . '"><i class="um-faicon-check"></i></span>';
				}
				else {
					echo __( 'Manual', 'um-mailchimp' );
				}
				break;

			case 'list_id':
				$list_id = get_post_meta( $id, '_um_list', true );
				echo $list_id;
				break;

			case 'subscribers':
				$list_id = get_post_meta( $id, '_um_list', true );
				echo UM()->Mailchimp()->api()->get_list_member_count( $list_id );
				break;

			case 'available_to':
				$roles = get_post_meta( $id, '_um_roles', true );
				$res = __( 'Everyone', 'um-mailchimp' );
				if( $roles && is_array( $roles ) ) {
					$res = array();
					$data = UM()->roles()->get_roles();
					foreach( $roles as $role ) {
						$res[] = isset( $data[ $role ] ) ? $data[ $role ] : '';
					}
					echo implode( ", ", $res );
				}
				else {
					echo $res;
				}
				break;
		}
	}

}
