<?php
namespace um_ext\um_mailchimp\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Main API functionality
 *
 * @example UM()->classes['um_mailchimp_main_api']
 * @example UM()->Mailchimp()->api()
 */
class Api {


	/**
	 *
	 */
	const members_count_max = 1000;


	/**
	 * Enable/disable cache
	 * @var boolean
	 */
	private $cache = true;


	/**
	 * Is debug mod enabled?
	 * @var boolean
	 */
	public $debug_mod = false;


	/**
	 * Last error
	 * @var array
	 */
	private $error = null;


	/**
	 * Errors
	 * @var array
	 */
	private $errors = array();


	/**
	 * Object for process API requests
	 * @var \UM_MailChimp_V3
	 */
	private $mailchimp;


	/**
	 * How long to cache response
	 * @var int
	 */
	private $transient_time = 600;


	/**
	 * Requested member ID
	 * @var int
	 */
	public $user_id = null;


	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->cache = (bool) UM()->options()->get( 'mailchimp_enable_cache' );
		$this->transient_time = (int) UM()->options()->get( 'mailchimp_transient_time' );
		$this->user_id = get_current_user_id();

		// Groups
		add_filter( 'um_mailchimp_api_create_member', array( $this, 'filter_member_interests' ), 20, 4 );
		add_filter( 'um_mailchimp_api_update_member', array( $this, 'filter_member_interests' ), 20, 4 );

		// Merge Fields
		add_filter( 'um_mailchimp_api_create_member', array( $this, 'filter_member_merge_fields' ), 10, 4 );
		add_filter( 'um_mailchimp_api_update_member', array( $this, 'filter_member_merge_fields' ), 10, 4 );

		// Status
		add_filter( 'um_mailchimp_default_subscription_status', array( $this, 'filter_subscription_status' ), 10, 4 );


		// Tags
		add_filter( 'um_mailchimp_api_create_member_response', array( $this, 'filter_member_tags' ), 30, 4 );
		add_filter( 'um_mailchimp_api_update_member_response', array( $this, 'filter_member_tags' ), 30, 4 );

		if ( isset( $_REQUEST['um_action'] ) && sanitize_key( $_REQUEST['um_action'] ) == 'um_mailchimp_clear_cache' ) {
			add_action( 'plugins_loaded', array( $this, 'clear_cache' ) );
		}
	}

	/**
	 * Class destructor
	 */
	public function __destruct() {
		if ( $this->errors ) {
			$user_id = $this->get_user_id();
			set_transient( "um_mc_api:errors_$user_id", $this->errors, 60 );
		}
	}

	/**
	 * Object for requests
	 *
	 * @return \WP_Error|\UM_MailChimp_V3
	 */
	public function call() {
		if ( is_object( $this->mailchimp ) ) {
			return $this->mailchimp;
		}

		$apikey = UM()->options()->get( 'mailchimp_api' );
		if ( ! $apikey ) {
			return new \WP_Error( 'um-mailchimp-empty-api-key', sprintf( __( '<a href="%s"><strong>Please enter your valid API key</strong></a> in settings.', 'um-mailchimp' ), admin_url( 'admin.php?page=um_options&tab=extensions&section=mailchimp' ) ) );
		}

		try {
			$result = new \UM_MailChimp_V3( $apikey );
			$this->mailchimp = $result;
		} catch ( \Exception $e ) {
			$result = new \WP_Error( 'um-mailchimp-api-error', $e->getMessage() );
		}

		return $result;
	}

	/**
	 * Clear all caches
	 *
	 * @global $wpdb
	 */
	public function clear_cache() {
		global $wpdb;

		$wpdb->query( "
			UPDATE {$wpdb->options}
			SET option_value = ''
			WHERE option_name LIKE '%um_mc_api%';" );

		$this->delete_temp_files();

		wp_safe_redirect( remove_query_arg( 'um_action' ) );
	}

	/**
	 * Temporary files quantity
	 *
	 * @return int
	 */
	public function count_log_files() {
		$files = glob( UM()->files()->upload_basedir . 'mailchimp*.log' );
		return count( (array) $files );
	}

	/**
	 * Temporary files quantity
	 *
	 * @return int
	 */
	public function count_temp_files() {
		$files = glob( UM()->files()->upload_basedir . 'temp/*um_mailchimp*' );
		return count( (array) $files );
	}

	/**
	 * Delete cache
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param string $subscriber_hash
	 */
	public function delete_cache( $list_id, $user = null, $subscriber_hash = null ) {
		$this->delete_member_cache( $list_id, $user, $subscriber_hash );
		$this->delete_segments_cache( $list_id, $user, $subscriber_hash );
		$this->delete_tags_cache( $list_id, $user, $subscriber_hash );
	}

	/**
	 * Delete member cache
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param string $subscriber_hash
	 */
	public function delete_member_cache( $list_id, $user = null, $subscriber_hash = null ) {

		if ( empty( $subscriber_hash ) ) {
			$subscriber_hash = $this->get_user_hash( $user );
		}

		delete_transient( "um_mc_api:lists/{$list_id}/members/{$subscriber_hash}" );
		delete_transient( "um_mc_api:lists/{$list_id}/members/{$subscriber_hash}/tags" );
		delete_transient( "um_mc_api:lists/{$list_id}/members/{$subscriber_hash}/segments" );
	}


	/**
	 * Delete segments cache
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param string $segment_id
	 */
	public function delete_segments_cache( $list_id, $user = null, $segment_id = null ) {

		delete_transient( "um_mc_api:lists/{$list_id}/segments?type=saved" );

		if ( $segment_id ) {
			delete_transient( "um_mc_api:lists/{$list_id}/segments/{$segment_id}/members" );
		}
	}


	/**
	 * Delete tags cache
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param string $subscriber_hash
	 */
	public function delete_tags_cache( $list_id, $user = null, $subscriber_hash = null ) {

		if ( empty( $subscriber_hash ) ) {
			$subscriber_hash = $this->get_user_hash( $user );
		}

		delete_transient( "um_mc_api:lists/{$list_id}/segments?type=static" );
		delete_transient( "um_mc_api:lists/{$list_id}/members/{$subscriber_hash}/tags" );
	}


	/**
	 * Delete temporary files
	 *
	 * @param int $delay - time delay in seconds
	 * @param string $filter - delete only files, that has this word in the name
	 * @return int
	 */
	public function delete_temp_files( $delay = 60, $filter = '' ) {

		$deleted = 0;
		$timestamp = time() - $delay;
		$files = glob( UM()->files()->upload_basedir . 'temp/*um_mailchimp*' );

		foreach ( (array) $files as $file ) {
			if ( is_file( $file ) ) {
				if( $filter && !substr_count( $file, $filter )){
					continue;
				}
				$fileatime = fileatime( $file );
				if ( $fileatime && $fileatime < $timestamp && empty( UM()->Mailchimp()->api()->debug_mod ) ) {
					unlink( $file );
					$deleted++;
				}
			}
		}

		return $deleted;
	}

	/**
	 * Fetch list
	 *
	 * @deprecated since 2.2
	 *
	 * @param $id
	 * @return bool|array
	 */
	public function fetch_list( $id ) {
		$setup = get_post( $id );
		if ( ! isset( $setup->post_title ) ) {
			return false;
		}
		$list[ 'id' ] = get_post_meta( $id, '_um_list', true );
		$list[ 'auto_register' ] = get_post_meta( $id, '_um_reg_status', true );
		$list[ 'description' ] = get_post_meta( $id, '_um_desc', true );
		$list[ 'register_desc' ] = get_post_meta( $id, '_um_desc_reg', true );
		$list[ 'name' ] = $setup->post_title;
		$list[ 'status' ] = get_post_meta( $id, '_um_status', true );
		$list[ 'merge_vars' ] = get_post_meta( $id, '_um_merge', true );
		$list[ 'roles' ] = get_post_meta( $id, '_um_roles', true );

		return $list;
	}

	/**
	 * Update member's merge_fields.
	 *
	 * @hook um_mailchimp_api_create_member
	 * @hook um_mailchimp_api_update_member
	 *
	 * @param array $request_data
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $data [(WP_Post)'wp_list', (int)'wp_list_id']
	 * @return array
	 */
	public function filter_member_merge_fields( $request_data, $list_id, $user = null, $data = array() ) {

		$wp_list = null;
		if( isset( $data[ 'wp_list' ] ) && is_a( $data[ 'wp_list' ], 'WP_Post' ) ) {
			$wp_list = $data[ 'wp_list' ];
		} elseif( isset( $data[ 'wp_list_id' ] ) && is_numeric( $data[ 'wp_list_id' ] ) ) {
			$wp_list = get_post( $data[ 'wp_list_id' ] );
		} else {
			$wp_list = $this->get_wp_list( $list_id );
		}

		$merge_vars = $this->get_merge_vars( $list_id, $user, $wp_list );
		if ( $merge_vars ) {
			$request_data[ 'merge_fields' ] = $merge_vars;
		}

		return $request_data;
	}

	/**
	 * Update member's interests (groups).
	 *
	 * @hook um_mailchimp_api_create_member
	 * @hook um_mailchimp_api_update_member
	 *
	 * @param array $request_data
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $data [(array)'groups']
	 * @return array
	 */
	public function filter_member_interests( $request_data, $list_id, $user = null, $data = array() ) {

		if ( isset( $data[ 'groups' ] ) && is_array( $data[ 'groups' ] ) ) {
			$interests = array();
			foreach ( $data[ 'groups' ] as $group_id => $group_interests ) {
				$interests_array = $this->mc_get_interests_array( $list_id, $group_id );
				foreach ( $interests_array as $id => $name ) {
					$interests[ $id ] = in_array( $id, $group_interests );
				}
			}
			if( !empty( $interests ) ){
				$request_data[ 'interests' ] = $interests;
			}
		}

		return $request_data;
	}

	/**
	 * Update member's tags.
	 *
	 * @hook um_mailchimp_api_create_member_response
	 * @hook um_mailchimp_api_update_member_response
	 *
	 * @param array $response
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $data [(array)'tags', (string)'tags-new', (bool)'tags-update']
	 * @return array
	 */
	public function filter_member_tags( $response, $list_id, $user = null, $data = array() ) {

		// Tags
		$tags = array();
		if ( empty( $data[ 'tags' ] ) && isset( $data[ 'tags-update' ] ) ) {
			$data[ 'tags' ] = array();
		}
		if ( isset( $data[ 'tags' ] ) ) {
			$tags = $this->prepare_tags( $list_id, $data[ 'tags' ] );
		}

		// Add New Tags
		if ( !empty( $data[ 'tags-new' ] ) && UM()->options()->get( 'mailchimp_allow_add_tags' ) ) {
			$tags_new = array_map( 'trim', explode( ',', $data[ 'tags-new' ] ) );
			foreach ( $tags_new as $name ) {
				$tags[] = array(
					"name"	 => $name,
					"status" => "active"
				);
			}
		}

		if ( $tags ) {
			$this->mc_update_member_tags( $list_id, $user, $tags );
		}

		return $response;
	}


	/**
	 * Get default status
	 * 
	 * @global \um_ext\um_mailchimp\core\$wpdb $wpdb
	 * @param string $status
	 * @param string $action
	 * @param string $list_id
	 * @param string $email
	 * @return string
	 */
	public function filter_subscription_status( $status, $action, $list_id, $email = null ) {
		global $wpdb;

		if( empty( $status ) && $action === 'create' ){
			$double_optin = $wpdb->get_var( "
				SELECT pm.meta_value
				FROM {$wpdb->postmeta} AS pm,
				INNER JOIN {$wpdb->postmeta} AS pm2 ON (pm.post_id = pm2.post_id)
				WHERE pm2.post_id = pm.post_id
					AND pm.meta_key = '_um_double_optin'
					AND pm2.meta_key = '_um_list'
					AND pm2.meta_value = '{$list_id}';" );

			if ( $double_optin == '1' ) {
				$status = 'pending';
			} elseif ( $double_optin == '' && UM()->options()->get( 'mailchimp_double_optin' ) ) {
				$status = 'pending';
			}
		}

		if( empty( $status ) ){
			$status = 'subscribed';
		}

		return $status;
	}
	

	/**
	 * Retrieve connection
	 *
	 * @return array
	 */
	public function get_account_data() {
		$result = $this->call();
		if ( !is_wp_error( $result ) ) {
			$result = $result->get( '' );
		}

		return $result;
	}


	/**
	 * Get list subscriber count
	 *
	 * @param $list_id
	 * @return int|mixed|string
	 */
	public function get_list_member_count( $list_id ) {
		$list_data = $this->mc_get_list( $list_id );
		return isset( $list_data[ 'stats' ][ 'member_count' ] ) ? $list_data[ 'stats' ][ 'member_count' ] : 0;
	}


	/**
	 * Get list names
	 *
	 * @param bool $raw
	 * @return array
	 */
	public function get_lists( $raw = true ) {

		$lists = array();

		if ( $raw ) { // created from MailChimp
			$lists = $this->mc_get_lists();
		}
		else { // created from post type 'um_mailchimp'
			$wp_lists = $this->get_wp_lists();
			foreach ( $wp_lists as $wp_list ) {
				$lists[] = array(
					'id'	 => $wp_list->list_id,
					'name' => $wp_list->post_title
				);
			}
		}

		$res = array();
		if ( is_array( $lists ) ) {
			foreach ( $lists as $list ) {
				$res[ $list[ 'id' ] ] = $list[ 'name' ];
			}
		}

		return $res;
	}


	/**
	 * Get user audiences
	 *
	 * @param int|string|\WP_User $user
	 * @return array
	 */
	public function get_lists_my( $user = null ) {
		static $lists = array();

		$user_id = $this->get_user_id( $user );
		if ( isset( $lists[ $user_id ] ) ) {
			return $lists[ $user_id ];
		}

		$my_lists = array();
		$wp_lists = $this->get_wp_lists( true );

		foreach ( $wp_lists as $wp_list ) {
			$my_lists[ $wp_list->_um_list ] = $this->is_subscribed( $wp_list->_um_list, $user );
		}

		$lists[ $user_id ] = apply_filters( 'um_mailchimp_api_get_lists_my', $my_lists, $user_id );
		return $lists[ $user_id ];
	}


	/**
	 * Prepare user merge vars
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param \WP_Post $wp_list
	 * @param array $merge_vars
	 * @return array
	 */
	public function get_merge_vars( $list_id, $user = null, $wp_list = null, $merge_vars = array() ) {

		if( empty( $wp_list ) || !is_a( $wp_list, 'WP_Post' ) ){
			$wp_list = $this->get_wp_list( $list_id );
		}
		if( !is_array( $merge_vars ) ){
			$merge_vars = array();
		}

		$_um_merge = $wp_list->_um_merge;

		if ( $_um_merge && is_array( $_um_merge ) ) {
			$user_id = $this->get_user_id( $user );
			$userdata = get_userdata( $user_id );
			um_fetch_user( $user_id );

			foreach ( $_um_merge as $meta => $var ) {
				if ( !empty( $meta ) && !empty( $var ) ) {
					$val = empty( $userdata->$var ) ? um_user( $var ) : $userdata->$var;
					if ( empty( $val ) ) {
						continue;
					}

					if ( is_array( $val ) ) {
						$merge_vars[ $meta ] = current( $val );
					} else {
						$merge_vars[ $meta ] = $val;
					}
				}
			}
		}

		return apply_filters( 'um_mailchimp_single_merge_fields', $merge_vars, $user_id, $list_id, $_um_merge );
	}


	/**
	 * Get merge vars for a specific audience
	 *
	 * @deprecated
	 *
	 * @param $list_id
	 * @return array
	 */
	public function get_vars( $list_id ) {
		return $this->mc_get_merge_fields( $list_id );
	}


	/**
	 * Get audience WP_Post by mailchimp ID
	 *
	 * @param string $list_id
	 * @param boolean|string $role - Filter audiences by '_um_roles' property
	 * @return \WP_Post
	 */
	public function get_wp_list( $list_id, $role = null ) {
		$wp_lists = get_posts( array(
			'post_type'		 => 'um_mailchimp',
			'post_status'	 => 'publish',
			'meta_key'		 => '_um_list',
			'meta_value'	 => $list_id
			) );

		$roles = is_string( $role ) ? array( $role ) : um_user( 'roles' );
		foreach ( $wp_lists as $wp_list ) {
			if ( is_array( $wp_list->_um_roles ) && array_intersect( $roles, $wp_list->_um_roles ) ) {
				$wp_list->list_id = $wp_list->_um_list;
				return $wp_list;
			}
		}

		return current( $wp_lists );
	}


	/**
	 * Get user audiences as an array of the WP_Post
	 *
	 * @param boolean|string $role - Filter audiences by '_um_roles' property
	 * @return array
	 */
	public function get_wp_lists( $role = null ) {

		$wp_lists = array();

		$get_posts_args = array(
			'nopaging'		 => true,
			'order'				 => 'ASC',
			'orderby'			 => 'post_title',
			'post_type'		 => 'um_mailchimp',
			'post_status'	 => 'publish',
			'meta_query'	 => array(
				'relation' => 'AND',
				array(
					'key'			 => '_um_status',
					'value'		 => 1,
					'compare'	 => '='
				)
			)
		);

		$roles = is_string( $role ) ? array( $role ) : um_user( 'roles' );
		$wp_lists_raw = get_posts( $get_posts_args );

		foreach ( $wp_lists_raw as $wp_list ) {
			if ( $role && is_array( $wp_list->_um_roles ) && empty( array_intersect( $roles, $wp_list->_um_roles ) ) ) {
				continue;
			}

			$wp_list->list_id = $wp_list->_um_list;
			$wp_lists[ $wp_list->ID ] = $wp_list;
		}

		return $wp_lists;
	}


	/**
	 * Get user audiences as simple array
	 *
	 * @param boolean|string $role - Filter audiences by '_um_roles' property
	 * @return array
	 */
	public function get_wp_lists_array( $role = null ) {

		$options = array();

		$wp_lists = $this->get_wp_lists( $role );

		foreach ( $wp_lists as $wp_list ) {
			$options[ $wp_list->ID ] = $wp_list->post_title;
		}

		return apply_filters( 'um_mailchimp_get_wp_lists_array', $options, $role, $wp_lists );
	}


	/**
	 * Get user email
	 *
	 * @global \WP_User $current_user
	 * @param int|string|\WP_User $user
	 * @return string
	 */
	public function get_user_email( $user = null ) {
		global $current_user;

		$email = null;
		if ( empty( $user ) ) {
			$email = $current_user->user_email;
		}
		elseif ( is_string( $user ) && is_email( $user ) ) {
			$email = $user;
		}
		elseif ( is_numeric( $user ) ) {
			$user = get_userdata( $user );
			$email = $user->user_email;
		}
		elseif ( is_a( $user, 'WP_User' ) ) {
			$email = $user->user_email;
		}

		return apply_filters( 'um_mailchimp_api_get_user_email', $email, $user );
	}


	/**
	 * Get Subscriber Hash. The MD5 hash of the lowercase version of the list member's email address.
	 * 
	 * @param int|string|\WP_User $user
	 * @return string
	 */
	public function get_user_hash( $user = null ) {
		$email = $this->get_user_email( $user );
		$subscriber_hash = md5( strtolower( $email ) );

		return apply_filters( 'um_mailchimp_api_get_user_hash', $subscriber_hash, $user );
	}


	/**
	 * Get user ID
	 *
	 * @param null|int|string|\WP_User $user
	 * @return int|null
	 */
	public function get_user_id( $user = null ) {

		$user_id = null;
		if ( empty( $user ) ) {
			$user_id = $this->user_id;
		} elseif ( is_numeric( $user ) ) {
			$user_id = $user;
		} elseif ( is_string( $user ) && is_email( $user ) ) {
			$user_id = email_exists( $user );
		} elseif ( is_a( $user, 'WP_User' ) ) {
			$user_id = $user->ID;
		}

		if( $user_id !== $this->user_id ){
			$this->user_id = $user_id;
		}

		return $user_id;
	}


	/**
	 * Is user member?
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @return boolean
	 */
	public function is_member( $list_id, $user = null ) {

		$member = $this->mc_get_member( $list_id, $user );

		if ( isset( $member[ 'status' ] ) && is_string( $member[ 'status' ] ) ) {
			$status = true;
		}
		elseif ( isset( $member[ 'status' ] ) && is_numeric( $member[ 'status' ] ) ) {
			$status = false;
		}
		else {
			$status = null;
		}

		return $status;
	}


	/**
	 * Is user subscribed?
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @return boolean
	 */
	public function is_subscribed( $list_id, $user = null ) {

		$member = $this->mc_get_member( $list_id, $user );

		if ( isset( $member['status'] ) && $member['status'] == 'subscribed' ) {
			$status = true;
			$this->update_mylists( $list_id, $user, 'add' );
		} else {
			$status = false;
			$this->update_mylists( $list_id, $user, 'remove' );
		}

		return $status;
	}


	/**
	 * Create an audience member
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $data [(array)'groups', (string)'status', (array)'tags', (string)'tags-new', (bool)'tags-update', (WP_Post)'wp_list', (int)'wp_list_id']
	 * @return bool|array
	 */
	public function mc_create_member( $list_id, $user = null, $data = array() ) {

		$email = $this->get_user_email( $user );

		if ( $this->is_member( $list_id, $email )) {
			return $this->mc_update_member( $list_id, $email, $data );
		}

		$status = apply_filters( 'um_mailchimp_default_subscription_status', isset( $data['status'] ) ? $data['status'] : null, 'create', $list_id, $email );

		$request_data = apply_filters( 'um_mailchimp_api_create_member', array(
			'email_address'	 => $email,
			'status'				 => $status
		), $list_id, $user, $data );

		$request = "lists/{$list_id}/members";

		$response = $this->call()->post( $request, $request_data );

		if ( isset( $response[ 'status' ] ) && is_numeric( $response[ 'status' ] ) ) {
			$this->errors[ "POST:$request" ] = $response;
			$this->error = $response;
			$response = false;
		}
		else {
			$this->update_mylists( $list_id, $user, 'add' );
		}

		$this->delete_member_cache( $list_id, $user );

		return apply_filters( 'um_mailchimp_api_create_member_response', $response, $list_id, $user, $data );
	}


	/**
	 * Delete an audience member
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#delete-delete_lists_list_id_members_subscriber_hash
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @return bool|array
	 */
	public function mc_delete_member( $list_id, $user = null ) {

		// if member doesn't exist
		if ( ! $this->is_member( $list_id, $user ) ) {
			return true;
		}

		$subscriber_hash = $this->get_user_hash( $user );

		$request = "lists/{$list_id}/members/{$subscriber_hash}";

		$response = $this->call()->delete( $request );

		if ( isset( $response['status'] ) && is_numeric( $response['status'] ) ) {
			$this->mc_unsubscribe_member( $list_id, $user );
			$this->errors[ "DELETE:$request" ] = $response;
			$this->error = $response;
			$response = false;
		} else {
			$this->update_mylists( $list_id, $user, 'remove' );
		}

		$this->delete_member_cache( $list_id, $user, $subscriber_hash );

		return $response;
	}


	/**
	 * Get interest categories from the audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/#read-get_lists_list_id_interest_categories
	 *
	 * @param string $list_id
	 * @param boolean $links
	 * @param boolean $interests
	 * @return array
	 */
	public function mc_get_interest_categories( $list_id, $links = false, $interests = true ) {

		$request = "lists/{$list_id}/interest-categories";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request );

		if ( empty( $response['categories'] ) || ! is_array( $response['categories'] ) ) {
			return array();
		}

		if ( $interests ) {
			foreach ( $response['categories'] as $i => $category ) {
				$response['categories'][ $i ]['interests'] = $this->mc_get_interests( $list_id, $category['id'] );
			}
		}

		if ( ! $links ) {
			$response['categories'] = UM()->Mailchimp()->log()->remove_links( $response['categories'] );
		}

		set_transient( "um_mc_api:$request", $response['categories'], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_interest_categories', $response['categories'], $list_id, $interests );
	}


	/**
	 * Get interest categories as simple array
	 *
	 * @param string $list_id
	 * @return array
	 */
	public function mc_get_interest_categories_array( $list_id ) {

		$options = array();

		$interest_categories = $this->mc_get_interest_categories( $list_id, false, false );

		foreach ( $interest_categories as $interest_category ) {
			$options[ $interest_category['id'] ] = isset( $interest_category['name'] ) ? $interest_category['name'] : $interest_category['title'];
		}

		return apply_filters( 'um_mailchimp_api_get_interest_categories_array', $options, $list_id, $interest_categories );
	}


	/**
	 * Get interest from the interest category
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/interests/#read-get_lists_list_id_interest_categories_interest_category_id_interests
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_interests( $list_id, $interest_category_id, $links = false ) {

		$request = "lists/{$list_id}/interest-categories/$interest_category_id/interests";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request );

		if ( empty( $response['interests'] ) || ! is_array( $response['interests'] ) ) {
			return array();
		}

		if ( ! $links ) {
			$response['interests'] = UM()->Mailchimp()->log()->remove_links( $response['interests'] );
		}

		set_transient( "um_mc_api:$request", $response['interests'], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_interests', $response['interests'], $list_id, $interest_category_id );
	}


	/**
	 * Get interests as simple array
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @return array
	 */
	public function mc_get_interests_array( $list_id, $interest_category_id ) {

		$options = array();

		$interests = $this->mc_get_interests( $list_id, $interest_category_id );

		foreach ( $interests as $interest ) {
			$options[ $interest['id'] ] = $interest['name'];
		}

		return apply_filters( 'um_mailchimp_api_get_interests_array', $options, $list_id, $interest_category_id, $interests );
	}

	/**
	 * Get list/audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	 *
	 * @param int $list_id
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_list( $list_id, $links = false ) {

		$request = "lists/{$list_id}";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request );

		if ( ! $links ) {
			$response = UM()->Mailchimp()->log()->remove_links( $response );
		}

		set_transient( "um_mc_api:$request", $response, $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_list', $response );
	}


	/**
	 * Get lists/audiences
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	 *
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_lists( $links = false ) {

		$request = "lists";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request, array(
			'count' => apply_filters( 'um_mailchimp_lists_limit', 100 )
			) );

		if ( empty( $response[ 'lists' ] ) || !is_array( $response[ 'lists' ] ) ) {
			return array();
		}

		if ( !$links ) {
			$response[ 'lists' ] = UM()->Mailchimp()->log()->remove_links( $response[ 'lists' ] );
		}

		set_transient( "um_mc_api:$request", $response[ 'lists' ], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_lists', $response[ 'lists' ] );
	}

	/**
	 * Get member's data from the audience

	 * @global \WP_User $current_user
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_member( $list_id, $user = null, $links = false ) {

		$subscriber_hash = $this->get_user_hash( $user );

		$request = "lists/{$list_id}/members/{$subscriber_hash}";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request );

		if ( isset( $response[ "status" ] ) && $response[ "status" ] === 404 ) {
			$this->update_mylists( $list_id, $user, 'remove' );
		}

		if ( ! $links ) {
			$response = UM()->Mailchimp()->log()->remove_links( $response );
		}

		set_transient( "um_mc_api:$request", $response, $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_member', $response, $list_id, $user );
	}

	/**
	 * Get member's tags from the audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/tags/#read-get_lists_list_id_members_subscriber_hash_tags
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_member_tags( $list_id, $user = null, $links = false ) {

		$subscriber_hash = $this->get_user_hash( $user );

		$request = "lists/{$list_id}/members/{$subscriber_hash}/tags";

		$transient = get_transient( "um_mc_api:$request" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request );

		if ( empty( $response[ "tags" ] ) || !is_array( $response[ "tags" ] ) ) {
			return array();
		}

		if ( !$links ) {
			$response[ "tags" ] = UM()->Mailchimp()->log()->remove_links( $response[ "tags" ] );
		}

		set_transient( "um_mc_api:$request", $response[ "tags" ], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_member_tags', $response[ "tags" ], $list_id, $user );
	}

	/**
	 * Get member's tags as simple array
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @return array
	 */
	public function mc_get_member_tags_array( $list_id, $user = null ) {

		$options = array();

		$tags = $this->mc_get_member_tags( $list_id, $user );

		foreach ( $tags as $tag ) {
			$options[ $tag[ 'id' ] ] = $tag[ 'name' ];
		}

		return apply_filters( 'um_mailchimp_api_get_member_tags_array', $options, $list_id, $user, $tags );
	}

	/**
	 * Get list/audience members
	 *
	 * @param int $list_id
	 * @return array
	 */
	public function mc_get_members( $list_id ) {

		$count = ceil( self::members_count_max * 0.4 );
		$offset = 0;
		$members = array();

		$request = "lists/{$list_id}/members";

		do {

			$request_data = array(
					'count'	 => $count,
					'offset' => $offset,
					'status' => 'subscribed',
			);
			$hash = md5( json_encode( $request_data ), true );

			$transient = get_transient( "um_mc_api:$request?hash=$hash" );
			if( $this->cache && $transient ) {
				$members = array_merge( $members, ( array ) $transient );
				continue;
			}

			$response = $this->call()->get( $request, $request_data );

			if( isset( $response[ 'members' ] ) && is_array( $response[ 'members' ] ) ) {
				$offset += $count;
				$members_part = UM()->Mailchimp()->log()->remove_links( $response[ 'members' ] );
				$members = array_merge( $members, $members_part );
				set_transient( "um_mc_api:$request?hash=$hash", $members_part, $this->transient_time );
			}
			else {
				break;
			}
		} while( isset( $response[ 'total_items' ] ) && $offset < $response[ 'total_items' ] );

		return apply_filters( 'um_mailchimp_api_get_members', $members, $list_id );
	}


	/**
	 * Get merge vars for a specific audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#read-get_lists_list_id_merge_fields
	 *
	 * @param $list_id
	 * @param $links
	 * @return array
	 */
	public function mc_get_merge_fields( $list_id, $links = false ) {

		$request = "lists/{$list_id}/merge-fields";

		$transient = get_transient( "um_mc_api:$request" );
		if ( !is_admin() && $this->cache && $transient ) {
			return (array) $transient;
		}

		$response = $this->call()->get( $request, array(
				'count'	 => 100,
				'offset' => 0
		) );

		if ( empty( $response[ 'merge_fields' ] ) || !is_array( $response[ 'merge_fields' ] ) ) {
			return array();
		}

		if ( !$links ) {
			$response[ 'merge_fields' ] = UM()->Mailchimp()->log()->remove_links( $response[ 'merge_fields' ] );
		}

		set_transient( "um_mc_api:$request", $response[ 'merge_fields' ], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_interests', $response[ 'merge_fields' ], $list_id );
	}

	/**
	 * Get segment members
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/members/#read-get_lists_list_id_segments_segment_id_members
	 *
	 * @param string $list_id
	 * @param int $segment_id
	 * @return array
	 */
	public function mc_get_segment_members( $list_id, $segment_id ) {

		$count = ceil( self::members_count_max * 0.4 );
		$offset = 0;
		$members = array();

		$request = "lists/{$list_id}/segments/{$segment_id}/members";

		do {

			$request_data = array(
					'count'	 => $count,
					'offset' => $offset,
			);
			$hash = md5( json_encode( $request_data ), true );

			$transient = get_transient( "um_mc_api:$request?hash=$hash" );
			if ( $this->cache && $transient ) {
				$members = array_merge( $members, ( array ) $transient );
				continue;
			}

			$response = $this->call()->get( $request, $request_data );

			if ( isset( $response[ 'members' ] ) && is_array( $response[ 'members' ] ) ) {
				$offset += $count;
				$members_part = UM()->Mailchimp()->log()->remove_links( $response[ 'members' ] );
				$members = array_merge( $members, $members_part );
				set_transient( "um_mc_api:$request?hash=$hash", $members_part, $this->transient_time );
			} else {
				break;
			}
		} while( isset( $response[ 'total_items' ] ) && $offset < $response[ 'total_items' ] );

		return apply_filters( 'um_mailchimp_api_get_segment_members', $members, $list_id, $segment_id );
	}

	/**
	 * Get segments from the audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#read-get_lists_list_id_segments
	 *
	 * @param string $list_id
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_segments( $list_id, $links = false ) {

		$request = "lists/{$list_id}/segments";

		$transient = get_transient( "um_mc_api:$request?type=saved" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		/**
		 * Note:
		 * Possible values of the type of segment: saved, static, fuzzy.
		 * Static segments are now known as tags.
		 */
		$response = $this->call()->get( $request, array(
			'type' => 'saved'
			) );

		if ( empty( $response[ "segments" ] ) || !is_array( $response[ "segments" ] ) ) {
			return array();
		}

		if ( !$links ) {
			$response[ "segments" ] = UM()->Mailchimp()->log()->remove_links( $response[ "segments" ] );
		}

		set_transient( "um_mc_api:$request?type=saved", $response[ "segments" ], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_segments', $response[ "segments" ], $list_id );
	}

	/**
	 * Get segments as simple array
	 *
	 * @param string $list_id
	 * @return array
	 */
	public function mc_get_segments_array( $list_id ) {

		$options = array();

		$segments = $this->mc_get_segments( $list_id );

		foreach ( $segments as $segment ) {
			$options[ $segment[ 'id' ] ] = $segment[ 'name' ];
		}

		return apply_filters( 'um_mailchimp_api_get_segments_array', $options, $list_id, $segments );
	}

	/**
	 * Get tags from the audience
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#read-get_lists_list_id_segments
	 *
	 * @param string $list_id
	 * @param boolean $links
	 * @return array
	 */
	public function mc_get_tags( $list_id, $links = false ) {

		$request = "lists/{$list_id}/segments";

		$transient = get_transient( "um_mc_api:$request?type=static" );
		if ( $this->cache && $transient ) {
			return (array) $transient;
		}

		/**
		 * Note:
		 * Possible values of the type of segment: saved, static, fuzzy.
		 * Static segments are now known as tags.
		 */
		$response = $this->call()->get( $request, array(
			'type' => 'static'
			) );

		if ( empty( $response[ "segments" ] ) || !is_array( $response[ "segments" ] ) ) {
			return array();
		}

		if ( !$links ) {
			$response[ "segments" ] = UM()->Mailchimp()->log()->remove_links( $response[ "segments" ] );
		}

		set_transient( "um_mc_api:$request?type=static", $response[ "segments" ], $this->transient_time );

		return apply_filters( 'um_mailchimp_api_get_tags', $response[ "segments" ], $list_id );
	}

	/**
	 * Get tags as simple array
	 *
	 * @param string $list_id
	 * @return array
	 */
	public function mc_get_tags_array( $list_id ) {

		$options = array();

		$tags = $this->mc_get_tags( $list_id );

		foreach ( $tags as $tag ) {
			$options[ $tag[ 'id' ] ] = $tag[ 'name' ];
		}

		return apply_filters( 'um_mailchimp_api_get_tags_array', $options, $list_id, $tags );
	}

	/**
	 * Subscribe user to the audience with predefined groups and tags
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param null|\WP_Post $wp_list
	 * @return bool|array
	 */
	public function mc_subscribe_member( $list_id, $user = null, $wp_list = null ) {

		if ( empty( $wp_list ) || ! is_a( $wp_list, 'WP_Post' ) ) {
			$wp_list = $this->get_wp_list( $list_id );
		}

		if ( ! $wp_list->_um_status ) {
			return false;
		}

		$data = array(
			'status' => ($wp_list->_um_double_optin || UM()->options()->get( 'mailchimp_double_optin' )) ? 'pending' : 'subscribed',
			'groups' => $this->prepare_groups( $list_id, $wp_list ),
			'tags'   => empty( $wp_list->_um_reg_tags ) ? array() : $wp_list->_um_reg_tags
		);

		$result = $this->mc_update_member( $list_id, $user, $data );

		return $result;
	}

	/**
	 * Unsubscribe an audience member
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @return array|bool
	 */
	public function mc_unsubscribe_member( $list_id, $user = null ) {

		// if member doesn't exist
		if ( ! $this->is_member( $list_id, $user ) ) {
			return true;
		}

		$email = $this->get_user_email( $user );
		$subscriber_hash = $this->get_user_hash( $email );

		$request_data = array(
			'email_address'	 => $email,
			'status'				 => 'unsubscribed'
		);

		$request = "lists/{$list_id}/members/{$subscriber_hash}";

		$response = $this->call()->patch( $request, $request_data );

		if ( isset( $response[ 'status' ] ) && is_numeric( $response[ 'status' ] ) ) {
			$this->errors[ "PATCH:$request" ] = $response;
			$this->error = $response;
			$response = false;
		} else {
			$this->update_mylists( $list_id, $user, 'remove' );
		}

		$this->delete_member_cache( $list_id, $user, $subscriber_hash );

		return $response;
	}


	/**
	 * Update an audience member
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $data [(array)'groups', (string)'status', (array)'tags', (string)'tags-new', (bool)'tags-update', (WP_Post)'wp_list', (int)'wp_list_id']
	 * @return bool|array
	 */
	public function mc_update_member( $list_id, $user = null, $data = array() ) {
		global $um_mc_old_email;

		$email = $this->get_user_email( $user );
		$email_old = empty( $um_mc_old_email ) ? $email : $um_mc_old_email;
		$subscriber_hash = '';

		// create member if no exists
		if ( $email === $email_old ) {
			$is_member = $this->is_member( $list_id, $email );

			if ( $is_member ) {
				$subscriber_hash = $this->get_user_hash( $email );
			} else {
				return $this->mc_create_member( $list_id, $email, $data );
			}
		} else {
			$is_member = $this->is_member( $list_id, $email );
			$is_member_old = $this->is_member( $list_id, $email_old );

			if ( $is_member && $is_member_old ) {
				$subscriber_hash = $this->get_user_hash( $email );
				$this->mc_unsubscribe_member( $list_id, $email_old );
			} elseif ( $is_member && !$is_member_old ) {
				$subscriber_hash = $this->get_user_hash( $email );
			} elseif ( !$is_member && $is_member_old ) {
				$subscriber_hash = $this->get_user_hash( $email_old );
			} elseif ( !$is_member && !$is_member_old ) {
				return $this->mc_create_member( $list_id, $email, $data );
			}
		}

		$status = apply_filters( 'um_mailchimp_default_subscription_status', isset( $data[ 'status' ] ) ? $data[ 'status' ] : null, 'update', $list_id,  $email );

		$request_data = apply_filters( 'um_mailchimp_api_update_member', array(
			'email_address'	 => $email,
			'status'				 => $status
		), $list_id, $user, $data );

		$request = "lists/{$list_id}/members/{$subscriber_hash}";

		$response = $this->call()->patch( $request, $request_data );

		if ( isset( $response[ 'status' ] ) && is_numeric( $response[ 'status' ] ) ) {
			$this->errors[ "PATCH:$request" ] = $response;
			$this->error = $response;
			$response = false;
		} else {
			$action = $response[ 'status' ] === 'subscribed' ? 'add' : 'remove';
			$this->update_mylists( $list_id, $user, $action );
		}

		$this->delete_member_cache( $list_id, $user, $subscriber_hash );

		return apply_filters( 'um_mailchimp_api_update_member_response', $response, $list_id, $user, $data );
	}


	/**
	 * Add or remove tags from a list member.
	 * If a tag that does not exist is passed in and set as ‘active’, a new tag will be created.
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/tags/#create-post_lists_list_id_members_subscriber_hash_tags
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param array $tags
	 * @return array
	 */
	public function mc_update_member_tags( $list_id, $user = null, $tags = array() ) {

		$subscriber_hash = $this->get_user_hash( $user );

		$request_data = array(
			'tags' => $tags
		);

		$request = "lists/{$list_id}/members/{$subscriber_hash}/tags";

		$response = $this->call()->post( $request, apply_filters( 'um_mailchimp_api_update_member_tags', $request_data, $list_id, $user, $tags ) );

		if ( isset( $response[ 'status' ] ) && is_numeric( $response[ 'status' ] ) ) {
			$this->errors[ "POST:$request" ] = $response;
			$this->error = $response;
			$response = false;
		}

		$this->delete_tags_cache( $list_id, $user, $subscriber_hash );

		return apply_filters( 'um_mailchimp_api_update_member_tags_response', $response, $list_id, $user, $tags );
	}


	/**
	 * Batch add/remove list members to static segment
	 *
	 * @tutorial https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#create-post_lists_list_id_segments_segment_id
	 *
	 * @param string $list_id
	 * @param string $segment_id
	 * @param array $request_data [members_to_add, members_to_remove]
	 * @return array
	 */
	public function mc_update_segment_members( $list_id, $segment_id, $request_data ) {

		$request = "lists/{$list_id}/segments/{$segment_id}";

		$response = $this->call()->post( $request, apply_filters( 'um_mailchimp_api_update_segment_members', $request_data, $list_id, $segment_id ) );

		if ( isset( $response[ 'status' ] ) && is_numeric( $response[ 'status' ] ) ) {
			$this->errors[ "POST:$request" ] = $response;
			$this->error = $response;
			$response = false;
		}

		return apply_filters( 'um_mailchimp_api_update_segment_members_response', $response, $list_id, $segment_id );
	}


	/**
	 * Prepare custom merge vars
	 *
	 * @param array $merge_vars
	 * @return array
	 */
	public function prepare_data( $merge_vars = array() ) {
		if ( empty( $merge_vars ) ) {
			$merge_vars = array();
		}

		foreach ( $merge_vars as $key => $val ) {
			if ( ! empty( $val ) && is_array( $val ) ) {
				$merge_vars[ $key ] = implode( ', ', $val );
			} else {
				unset( $merge_vars[ $key ] );
			}
		}

		if ( isset( $merge_vars['email_address'] ) ) {
			unset( $merge_vars['email_address'] );
		}
		return $merge_vars;
	}


	/**
	 * Prepare custom groups
	 *
	 * @param string $list_id
	 * @param null|\WP_Post $wp_list
	 * @param array $groups
	 * @return array
	 */
	public function prepare_groups( $list_id, $wp_list = null, $groups = array() ) {

		if ( empty( $wp_list ) || !is_a( $wp_list, 'WP_Post' ) ) {
			$wp_list = $this->get_wp_list( $list_id );
		}

		$mc_groups = $this->mc_get_interest_categories_array( $list_id );
		foreach ( $mc_groups as $id => $name ) {
			$meta_key = "_um_reg_groups_$id";
			$groups[ $id ] = $wp_list->$meta_key;
		}

		return $groups;
	}


	/**
	 * Prepare custom tags
	 *
	 * @param string $list_id
	 * @param array $um_reg_tags
	 * @param array $tags
	 * @return array
	 */
	public function prepare_tags( $list_id, $um_reg_tags = array(), $tags = array() ) {

		if ( isset( $um_reg_tags ) && is_array( $um_reg_tags ) ) {
			$mc_tags = $this->mc_get_tags_array( $list_id );
			foreach ( $mc_tags as $id => $name ) {
				if ( in_array( $id, $um_reg_tags ) ) {
					$tags[] = array(
						'name'      => $name,
						'status'    => 'active',
					);
				} else {
					$tags[] = array(
						'name'      => $name,
						'status'    => 'inactive',
					);
				}
			}
		}

		return $tags;
	}

	/**
	 * Update user meta '_mylists'
	 *
	 * @param string $list_id
	 * @param int|string|\WP_User $user
	 * @param string $action - 'add' or 'remove'
	 * @return array
	 */
	public function update_mylists( $list_id, $user = null, $action = 'add' ) {

		$user_id = $this->get_user_id( $user );
		$my_lists = get_user_meta( $user_id, '_mylists', true );

		if ( ! is_array( $my_lists ) ) {
			$my_lists = array();
		}

		switch ( $action ) {
			case 'add':
				$my_lists[ $list_id ] = true;
				break;

			case 'remove':
				$my_lists[ $list_id ] = false;
				break;

			default:
				$my_lists[ $list_id ] = filter_var( $action, FILTER_SANITIZE_NUMBER_INT );
				break;
		}

		update_user_meta( $user_id, '_mylists', $my_lists );

		return $my_lists;
	}

}
