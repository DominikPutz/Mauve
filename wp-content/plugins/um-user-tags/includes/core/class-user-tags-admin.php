<?php
namespace um_ext\um_user_tags\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Admin
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Admin {


	/**
	 * User_Tags_Admin constructor.
	 */
	function __construct() {
		$this->pagehook = 'toplevel_page_ultimatemember';
		add_action( 'um_extend_admin_menu',  array( &$this, 'um_extend_admin_menu' ), 5 );
	}


	/**
	 * Add User Tags submenu
	 */
	function um_extend_admin_menu() {
		add_submenu_page( 'ultimatemember', __( 'User Tags', 'um-user-tags' ), __( 'User Tags', 'um-user-tags' ), 'manage_options', 'edit-tags.php?taxonomy=um_user_tag', '' );
	}


	/**
	 *
	 */
	function term_fields_create() {
		$data = array();

		//for metadata for all UM Member Directories
//also update for forms metadata where "member" or "admin"
		$forms_query = new \WP_Query;
		$member_directories = $forms_query->query( array(
			'post_type'         => 'um_directory',
			'posts_per_page'    => -1,
			'fields'            => array( 'ID', 'post_title' ),
		) );

		$directories = array(
			''  => __( '(None)', 'um-user-tags' ),
		);
		if ( ! empty( $member_directories ) && ! is_wp_error( $member_directories ) ) {
			foreach ( $member_directories as $directory ) {
				$directories[ $directory->ID ] = $directory->post_title;
			}
		}

		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_admin_category_access_settings_fields
		 * @description Settings fields for terms
		 * @input_vars
		 * [{"var":"$access_settings_fields","type":"array","desc":"Settings Fields"},
		 * {"var":"$data","type":"array","desc":"Settings Data"},
		 * {"var":"$screen","type":"string","desc":"Category Screen"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_filter( 'um_admin_category_access_settings_fields', 'function_name', 10, 3 );
		 * @example
		 * <?php
		 * add_filter( 'um_admin_category_access_settings_fields', 'my_admin_category_access_settings_fields', 10, 3 );
		 * function my_admin_category_access_settings_fields( $access_settings_fields, $data, $screen ) {
		 *     // your code here
		 *     $access_settings_fields[] = array(
		 *         'id'          => 'my-field-key',
		 *         'type'        => 'my-field-type',
		 *         'label'       => __( 'My field Label', 'ultimate-member' ),
		 *         'description' => __( 'My Field Description', 'ultimate-member' ),
		 *         'value'       => ! empty( $data['_um_custom_access_settings'] ) ? $data['_um_custom_access_settings'] : 0,
		 *     );
		 *     return $access_settings_fields;
		 * }
		 * ?>
		 */
		$fields = apply_filters( 'um_admin_user_tags_settings_fields', array(
			array(
				'id'            => '_um_base_directory',
				'type'          => 'select',
				'label'         => __( 'Base member directory', 'um-user-tags' ),
				'description'   => __( 'Select base member directory to use its settings for displaying users with this tag', 'um-user-tags' ),
				'value'         => ! empty( $data['_um_base_directory'] ) ? $data['_um_base_directory'] : '',
				'options'       => $directories,
			),
		), $data, 'create' );

		UM()->admin_forms( array(
			'class'             => 'um-term-fields um-third-column',
			'without_wrapper'   => true,
			'div_line'          => true,
			'fields'            => $fields
		) )->render_form();

		wp_nonce_field( basename( __FILE__ ), 'um_admin_save_user_tag_nonce' );
	}


	/**
	 * @param $term
	 */
	function term_fields_edit( $term ) {
		$termID = $term->term_id;

		$data = get_term_meta( $termID, '_um_base_directory', true );

		//for metadata for all UM Member Directories
//also update for forms metadata where "member" or "admin"
		$forms_query = new \WP_Query;
		$member_directories = $forms_query->query( array(
			'post_type'         => 'um_directory',
			'posts_per_page'    => -1,
			'fields'            => array( 'ID', 'post_title' ),
		) );

		$directories = array(
			''  => __( '(None)', 'um-user-tags' ),
		);
		if ( ! empty( $member_directories ) && ! is_wp_error( $member_directories ) ) {
			foreach ( $member_directories as $directory ) {
				$directories[ $directory->ID ] = $directory->post_title;
			}
		}

		/**
		 * UM hook
		 *
		 * @type filter
		 * @title um_admin_category_access_settings_fields
		 * @description Settings fields for terms
		 * @input_vars
		 * [{"var":"$access_settings_fields","type":"array","desc":"Settings Fields"},
		 * {"var":"$data","type":"array","desc":"Settings Data"},
		 * {"var":"$screen","type":"string","desc":"Category Screen"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_filter( 'um_admin_category_access_settings_fields', 'function_name', 10, 3 );
		 * @example
		 * <?php
		 * add_filter( 'um_admin_category_access_settings_fields', 'my_admin_category_access_settings_fields', 10, 3 );
		 * function my_admin_category_access_settings_fields( $access_settings_fields, $data, $screen ) {
		 *     // your code here
		 *     $access_settings_fields[] = array(
		 *         'id'          => 'my-field-key',
		 *         'type'        => 'my-field-type',
		 *         'label'       => __( 'My field Label', 'ultimate-member' ),
		 *         'description' => __( 'My Field Description', 'ultimate-member' ),
		 *         'value'       => ! empty( $data['_um_custom_access_settings'] ) ? $data['_um_custom_access_settings'] : 0,
		 *     );
		 *     return $access_settings_fields;
		 * }
		 * ?>
		 */
		$fields = apply_filters( 'um_admin_user_tags_settings_fields', array(
			array(
				'id'            => '_um_base_directory',
				'type'          => 'select',
				'label'         => __( 'Base member directory', 'um-user-tags' ),
				'description'   => __( 'Select base member directory to use its settings for displaying users with this tag', 'um-user-tags' ),
				'value'         => ! empty( $data ) ? $data : '',
				'options'       => $directories,
			),
		), $data, 'edit' );

		UM()->admin_forms( array(
			'class'             => 'um-restrict-content um-third-column',
			'without_wrapper'   => true,
			'fields'            => $fields
		) )->render_form();

		wp_nonce_field( basename( __FILE__ ), 'um_admin_save_user_tag_nonce' );
	}


	/**
	 * @param $termID
	 *
	 * @return mixed
	 */
	function term_fields_save( $termID ) {

		// validate nonce
		if ( ! isset( $_REQUEST['um_admin_save_user_tag_nonce'] ) || ! wp_verify_nonce( $_REQUEST['um_admin_save_user_tag_nonce'], basename( __FILE__ ) ) ) {
			return $termID;
		}

		// validate user
		$term = get_term( $termID );
		$taxonomy = get_taxonomy( $term->taxonomy );

		if ( ! current_user_can( $taxonomy->cap->edit_terms, $termID ) ) {
			return $termID;
		}

		if ( ! empty( $_REQUEST['_um_base_directory'] ) ) {
			update_term_meta( $termID, '_um_base_directory', $_REQUEST['_um_base_directory'] );
		} else {
			delete_term_meta( $termID, '_um_base_directory' );
		}

		return $termID;
	}
}