<?php
namespace um_ext\um_woocommerce\admin\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'um_ext\um_woocommerce\admin\core\Admin' ) ) {


	/**
	 * Class Admin
	 * @package um_ext\um_woocommerce\admin\core
	 */
	class Admin {


		/**
		 * Admin constructor.
		 */
		function __construct() {
			add_action( 'add_meta_boxes', array( &$this, 'add_product_metabox' ), 1 );
			add_filter( 'um_admin_role_metaboxes', array( &$this, 'add_role_metabox' ), 10, 1 );
			add_action( 'save_post', array( &$this, 'save_metabox_product_settings' ), 10, 2 );

			add_filter( 'um_settings_structure', array( &$this, 'woocommerce_settings' ), 10, 1 );
		}


		/**
		 * Extend settings
		 *
		 * @param $settings
		 *
		 * @return mixed
		 */
		function woocommerce_settings( $settings ) {

			$settings['licenses']['fields'][] = array(
				'id'      		=> 'um_woocommerce_license_key',
				'label'    		=> __( 'Woocommerce License Key', 'um-woocommerce' ),
				'item_name'     => 'WooCommerce',
				'author' 	    => 'Ultimate Member',
				'version' 	    => um_woocommerce_version,
			);

			$key = ! empty( $settings['extensions']['sections'] ) ? 'woocommerce' : '';
			$settings['extensions']['sections'][$key] = array(
				'title'     => __( 'Woocommerce', 'um-woocommerce' ),
				'fields'    => array(
					array(
						'id'       		=> 'woo_remove_roles',
						'type'     		=> 'select',
						'label'    		=> __( 'Remove previous roles when change role on complete or refund payment', 'um-woocommerce' ),
						'tooltip'    	=> __( 'If yes then remove all users roles and add current, else add current role to other roles, which user already has', 'um-woocommerce' ),
						'options' 		=> array( 0 => __( 'No', 'um-woocommerce' ), 1 => __( 'Yes', 'um-woocommerce' ) ),
						'size'          => 'small',
					),
					array(
						'id'       		=> 'woo_oncomplete_role',
						'type'     		=> 'select',
						'label'    		=> __( 'Assign this role to users when payment is completed', 'um-woocommerce' ),
						'tooltip' 	    => __( 'Automatically set the user this role when a payment is completed.', 'um-woocommerce' ),
						'options' 		=> array( '' => __( 'None', 'um-woocommerce' ) ) + UM()->roles()->get_roles(),
						'placeholder' 	=> __( 'Community role...', 'um-woocommerce' ),
						'size' => 'small',
					),
					array(
						'id'       		=> 'woo_oncomplete_except_roles',
						'type'     		=> 'select',
						'label'    		=> __( 'Do not update these roles when payment is completed', 'um-woocommerce' ),
						'tooltip' 	=> __( 'Only applicable if you assigned a role when payment is completed.', 'um-woocommerce' ),
						'options' 		=> UM()->roles()->get_roles(),
						'placeholder' 	=> __( 'Community role(s)..', 'um-woocommerce' ),
						'multi'         => true,
						'size' => 'small'
					),
					array(
						'id'       		=> 'woo_onhold_change_roles',
						'type'     		=> 'select',
						'label'    		=> __( 'Upgrade user role when payment is on-hold before complete or processing status', 'um-woocommerce' ),
						'options' 		=> array( 0 => __( 'No', 'um-woocommerce' ), 1 => __( 'Yes', 'um-woocommerce' ) ),
						'size'          => 'small',
						'conditional'	=> array( 'woo_oncomplete_role', '!=', '' )
					),
					array(
						'id'       		=> 'woo_onrefund_role',
						'type'     		=> 'select',
						'label'    		=> __( 'Assign this role to users when payment is refunded', 'um-woocommerce' ),
						'tooltip' 	    => __( 'Automatically set the user this role when a payment is refunded.', 'um-woocommerce' ),
						'options' 		=> array( '' => __( 'None', 'um-woocommerce' ) ) + UM()->roles()->get_roles(),
						'placeholder' 	=> __( 'Community role...', 'um-woocommerce' ),
						'size'          => 'small',
					),
					array(
						'id'       		=> 'woo_hide_billing_tab_from_account',
						'type'     		=> 'checkbox',
						'label'   		=> __( 'Hide billing tab from members in account page','um-woocommerce' ),
						'tooltip' 	=> __( 'Enable this option If you do not want to show the billing tab from members in account page.', 'um-woocommerce' ),
					),
					array(
						'id'       		=> 'woo_hide_shipping_tab_from_account',
						'type'     		=> 'checkbox',
						'label'   		=> __( 'Hide shipping tab from members in account page','um-woocommerce' ),
						'tooltip' 	=> __( 'Enable this option If you do not want to show the shipping tab from members in account page.', 'um-woocommerce' ),
					)
				)
			);

			return $settings;
		}


		/**
		 *
		 */
		function add_product_metabox() {
			add_meta_box(
				'um-admin-product-settings',
				__( 'Ultimate Member', 'um-woocommerce' ),
				array( &$this, 'load_metabox_product' ),
				'product',
				'normal',
				'default'
			);
		}


		/**
		 * @param $object
		 * @param $box
		 */
		function load_metabox_product( $object, $box ) {
			global $post;

			$box['id'] = str_replace( 'um-admin-product-','', $box['id'] );

			preg_match('#\{.*?\}#s', $box['id'], $matches);

			if ( isset( $matches[0] ) ) {
				$path = $matches[0];
				$box['id'] = preg_replace('~(\\{[^}]+\\})~','', $box['id'] );
			} else {
				$path = um_woocommerce_path;
			}

			$path = str_replace('{','', $path );
			$path = str_replace('}','', $path );

			include_once $path . 'includes/admin/templates/product/'. $box['id'] . '.php';
		}


		/**
		 * Create role options
		 *
		 * @param $roles_metaboxes
		 *
		 * @return array
		 */
		function add_role_metabox( $roles_metaboxes ) {
			$roles_metaboxes[] = array(
				'id'        => "um-admin-form-woocommerce{" . um_woocommerce_path . "}",
				'title'     => __( 'WooCommerce', 'um-woocommerce' ),
				'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
				'screen'    => 'um_role_meta',
				'context'   => 'normal',
				'priority'  => 'default'
			);

			return $roles_metaboxes;
		}


		/**
		 * Add handler for save metabox fields content
		 *
		 * @param $post_id
		 * @param $post
		 *
		 * @return mixed
		 */
		function save_metabox_product_settings( $post_id, $post ) {
			//make this handler only on product form submit
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( empty( $_REQUEST['_wpnonce'] ) ) {
				return $post_id;
			}

			if ( empty( $post->post_type ) || 'product' != $post->post_type ) {
				return $post_id;
			}

			if ( ! empty( $_POST['_um_woo_product_role'] ) ) {
				update_post_meta( $post_id, '_um_woo_product_role', sanitize_text_field( $_POST['_um_woo_product_role'] ) );
			} else {
				delete_post_meta( $post_id, '_um_woo_product_role' );
			}

			if ( function_exists( 'wcs_get_subscription' ) ) {
				if ( ! empty( $_POST['_um_woo_product_activated_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_activated_role', sanitize_text_field( $_POST['_um_woo_product_activated_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_activated_role' );
				}

				if ( ! empty( $_POST['_um_woo_product_downgrade_pending_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_downgrade_pending_role', sanitize_text_field( $_POST['_um_woo_product_downgrade_pending_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_downgrade_pending_role' );
				}

				if ( ! empty( $_POST['_um_woo_product_downgrade_onhold_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_downgrade_onhold_role', sanitize_text_field( $_POST['_um_woo_product_downgrade_onhold_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_downgrade_onhold_role' );
				}

				if ( ! empty( $_POST['_um_woo_product_downgrade_expired_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_downgrade_expired_role', sanitize_text_field( $_POST['_um_woo_product_downgrade_expired_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_downgrade_expired_role' );
				}

				if ( ! empty( $_POST['_um_woo_product_downgrade_cancelled_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_downgrade_cancelled_role', sanitize_text_field( $_POST['_um_woo_product_downgrade_cancelled_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_downgrade_cancelled_role' );
				}

				if ( ! empty( $_POST['_um_woo_product_downgrade_pendingcancel_role'] ) ) {
					update_post_meta( $post_id, '_um_woo_product_downgrade_pendingcancel_role', sanitize_text_field( $_POST['_um_woo_product_downgrade_pendingcancel_role'] ) );
				} else {
					delete_post_meta( $post_id, '_um_woo_product_downgrade_pendingcancel_role' );
				}
			}

			return $post_id;
		}

	}
}