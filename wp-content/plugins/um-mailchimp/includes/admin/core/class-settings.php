<?php
namespace um_ext\um_mailchimp\admin\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class for admin side functionality
 *
 * @example UM()->classes['um_mailchimp_admin']
 * @example UM()->Mailchimp()->admin()->settings
 */
class Settings {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ), 10, 1 );
		add_filter( 'um_change_settings_before_save', array( &$this, 'change_settings_before_save' ), 10, 1 );
		add_filter( 'um_email_registration_data', array( &$this, 'email_registration_data' ) );
		add_filter( 'um_render_field_type_mailchimp_api_key', array( &$this, 'render_field_type_mailchimp_api_key' ), 10, 4 );
		add_filter( 'um_render_field_type_mailchimp_log',array( &$this,  'render_field_type_mailchimp_log' ) );
	}


	/**
	 * Add fields to the page
	 *
	 * @param array $settings
	 * @return array
	 */
	function extend_settings( $settings ) {

		$settings['licenses']['fields'][] = array(
			'id'        => 'um_mailchimp_license_key',
			'label'     => __( 'MailChimp License Key', 'um-mailchimp' ),
			'item_name' => 'MailChimp',
			'author'    => 'Ultimate Member',
			'version'   => um_mailchimp_version,
		);

		$key = ! empty( $settings['extensions']['sections'] ) ? 'mailchimp' : '';

		$settings['extensions']['sections'][ $key ] = array(
			'title'     => __( 'MailChimp', 'um-mailchimp' ),
			'fields'    => array(
				array(
					'id'        => 'mailchimp_api',
					'type'      => 'mailchimp_api_key',
					'label'     => __( 'MailChimp API Key', 'um-mailchimp' ),
					'tooltip'   => __( 'The MailChimp API Key is required and enables you access and integration with your audiences.', 'um-mailchimp' ),
					'size'      => 'medium',
				),
				array(
					'id'        => 'mailchimp_unsubscribe_delete',
					'type'      => 'checkbox',
					'label'     => __( 'Remove subscriber from Mailchimp audience when user unsubscribed', 'um-mailchimp' ),
					'tooltip'   => __( 'If set option then subscriber will be removed from Mailchimp audience', 'um-mailchimp' ),
				),
				array(
					'id'        => 'mailchimp_allow_add_tags',
					'type'      => 'checkbox',
					'label'     => __( 'Allow member to create tags', 'um-mailchimp' ),
					'tooltip'   => __( 'Allow member to add new tags to the audience on subscribe.', 'um-mailchimp' ),
				),
				array(
					'id'        => 'mailchimp_double_optin',
					'type'      => 'checkbox',
					'label'     => __( 'Enable double opt-in', 'um-mailchimp' ),
					'tooltip'   => __( 'Send contacts an opt-in confirmation email when they subscribe to your audience.', 'um-mailchimp' ),
				),
				array(
					'id'        => 'mailchimp_enable_cache',
					'type'      => 'checkbox',
					'label'     => __( 'Enable requests cache', 'um-mailchimp' ),
					'tooltip'   => __( 'Cache MailChimp API requests to increase performance.', 'um-mailchimp' ),
				),
				array(
					'id'            => 'mailchimp_transient_time',
					'type'          => 'text',
					'label'         => __( 'Cache timeout, s', 'um-mailchimp' ),
					'tooltip'       => __( 'How long to cache MailChimp API requests.', 'um-mailchimp' ),
					'size'          => 'medium',
					'conditional'   => array( 'mailchimp_enable_cache', '=', '1' )
				),
				array(
					'id'        => 'mailchimp_enable_log',
					'type'      => 'checkbox',
					'label'     => __( 'Enable requests log', 'um-mailchimp' ),
					'tooltip'   => __( 'Log all requests to mailchimp server and save to wp-content/uploads/ultimatemember/mailchimp.log.', 'um-mailchimp' ),
				),
				array(
					'id'            => 'mailchimp_enable_log_response',
					'type'          => 'checkbox',
					'label'         => __( 'Log response for all requests', 'um-mailchimp' ),
					'tooltip'       => __( 'Log response for successful and failed requests. By default only response for failed request is logged.', 'um-mailchimp' ),
					'conditional'   => array( 'mailchimp_enable_log', '=', '1' )
				),
				array(
					'id'            => 'mailchimp_log',
					'type'          => 'mailchimp_log',
					'label'         => __( 'Requests log', 'um-mailchimp' ),
					'tooltip'       => __( 'MailChimp API requests and responses', 'um-mailchimp' ),
					'conditional'   => array( 'mailchimp_enable_log', '=', '1' ),
					'without_label' => false,
				),
			),
		);

		return $settings;
	}


	/**
	 * Reset cache if api key was changed
	 * @param array $settings
	 *
	 * @return array
	 */
	function change_settings_before_save( $settings ) {
		if ( isset( $settings['mailchimp_api'] ) && UM()->options()->get( 'mailchimp_api' ) != $settings['mailchimp_api'] ) {
			delete_transient( '_um_mailchimp_valid_api_key' );
		}

		return $settings;
	}


	/**
	 * Tweak parameters passed in admin email
	 *
	 * @param array $data
	 * @return array
	 */
	function email_registration_data( $data ) {
		if ( isset( $data['um-mailchimp'] ) ) {
			$array_lists = array();
			foreach ( $data['um-mailchimp'] as $list_id => $val ) {
				$posts = get_posts( array( 'post_type' => 'um_mailchimp', 'meta_key' => '_um_list', 'meta_value' => $list_id ) );
				if ( isset( $posts[0]->post_title ) ) {
					$array_lists[] = $posts[0]->post_title . '(#' . $list_id . ')';
				}
			}
			$data[ __( 'Mailchimp Subscription', 'um-mailchimp' ) ] = implode( ", ", $array_lists );
			unset( $data[ 'um-mailchimp' ] );
		}
		return $data;
	}


	/**
	 * Render field type "mailchimp_api_key"
	 *
	 * @param string $html
	 * @param array $data
	 * @param array $form_data
	 * @param \um\admin\core\Admin_Forms $admin_form
	 *
	 * @return string
	 */
	function render_field_type_mailchimp_api_key( $html, $data, $form_data, $admin_form ) {
		$html .= $admin_form->render_text( $data );
		$apikey = UM()->options()->get( 'mailchimp_api' );
		if ( ! $apikey ) {
			return $html;
		}

		$check_valid_key = get_transient( '_um_mailchimp_valid_api_key' );
		if( $check_valid_key === false ) {
			$api = UM()->Mailchimp()->api()->call();
			if( is_wp_error( $api ) ) {
				$check_valid_key = array( 'is_valid' => '0', 'error' => $api->get_error_message() );
			} else {
				$common_request = $api->get();
				if( !empty( $common_request[ 'account_id' ] ) ) {
					$check_valid_key = array( 'is_valid' => '1' );
				} else {
					$check_valid_key = array( 'is_valid' => '0', 'error' => '' );
					$check_valid_key['error'] .= ! empty( $common_request['title'] ) ? $common_request['title'] . '. ' : '';
					$check_valid_key['error'] .= ! empty( $common_request['detail'] ) ? $common_request['detail'] : '';
				}
				set_transient( '_um_mailchimp_valid_api_key', $check_valid_key, 24 * 3600 );
			}
		}

		if( $check_valid_key[ 'is_valid' ] == '1' ) {
			$html .= '<div class="dashicons dashicons-yes" style="color: green;"></div>';
		} else {
			$html .= '<br /><div class="dashicons dashicons-no-alt" style="color: red;"></div> ';
			$html .= !empty( $check_valid_key[ 'error' ] ) ? $check_valid_key[ 'error' ] : '';
		}

		return $html;
	}


	/**
	 * Render field type "mailchimp_log"
	 *
	 * @return string
	 */
	function render_field_type_mailchimp_log() {
		if ( ! UM()->options()->get( 'mailchimp_enable_log' ) ) {
			return '';
		}
		ob_start();
		?>

		<p><button class="button" id="um_mailchimp_clear_log"><?php _e( 'Clear log', 'um-mailchimp' ) ?></button></p>
		<div style="background-color: #ffffff; border: 1px solid #ddd; box-shadow: inset 0 1px 2px rgba(0,0,0,.07); max-height: 30em; min-height: 3em; overflow-y: auto; padding: 0.5em; word-break: break-all;"><?php echo UM()->Mailchimp()->log()->get_html(); ?></div>

		<?php
		return ob_get_clean();
	}


}
